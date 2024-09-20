<?php

namespace Spatie\TypeScriptTransformer;

use Closure;
use Exception;
use Spatie\TypeScriptTransformer\Actions\ParseUserDefinedTypeAction;
use Spatie\TypeScriptTransformer\Formatters\Formatter;
use Spatie\TypeScriptTransformer\Support\Extensions\TypeScriptTransformerExtension;
use Spatie\TypeScriptTransformer\Transformers\Transformer;
use Spatie\TypeScriptTransformer\TypeProviders\TransformerTypesProvider;
use Spatie\TypeScriptTransformer\TypeProviders\TypesProvider;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptRaw;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptUnknown;
use Spatie\TypeScriptTransformer\Visitor\Common\ReplaceTypesVisitorClosure;
use Spatie\TypeScriptTransformer\Visitor\VisitorClosure;
use Spatie\TypeScriptTransformer\Visitor\VisitorClosureType;
use Spatie\TypeScriptTransformer\Writers\NamespaceWriter;
use Spatie\TypeScriptTransformer\Writers\Writer;
use Throwable;

class TypeScriptTransformerConfigFactory
{
    /**
     * @param array<TypesProvider|string> $typeProviders
     * @param array<Transformer|string> $transformers
     * @param array<string> $directoriesToWatch
     * @param array<class-string|string, TypeScriptNode> $typeReplacements
     * @param array<class-string<TypeScriptTransformerExtension>, TypeScriptTransformerExtension> $extensions
     * @param array<VisitorClosure> $providedVisitorClosures
     * @param array<VisitorClosure> $connectedVisitorClosures
     */
    public function __construct(
        protected array $typeProviders = [],
        protected string|Writer|null $writer = null,
        protected string|Formatter|null $formatter = null,
        protected array $transformers = [],
        protected array $directoriesToWatch = [],
        protected array $typeReplacements = [],
        protected array $extensions = [],
        protected array $providedVisitorClosures = [],
        protected array $connectedVisitorClosures = [],
    ) {
    }

    public static function create(): self
    {
        return new self();
    }

    public function typesProvider(TypesProvider|string ...$typesProvider): self
    {
        foreach ($typesProvider as $provider) {
            if ($provider === TransformerTypesProvider::class || $provider instanceof TransformerTypesProvider) {
                throw new Exception("Please add transformers using the config's `transformer` method.");
            }
        }

        array_push($this->typeProviders, ...$typesProvider);

        return $this;
    }

    public function transformer(string|Transformer ...$transformer): self
    {
        array_push($this->transformers, ...$transformer);

        return $this;
    }

    public function prependTransformer(string|Transformer ...$transformer): self
    {
        array_unshift($this->transformers, ...$transformer);

        return $this;
    }

    public function replaceTransformer(
        string|Transformer $search,
        string|Transformer $replacement
    ): self {
        $searchClass = is_string($search) ? $search : $search::class;

        foreach ($this->transformers as $key => $transformer) {
            if (is_string($transformer) && $transformer === $searchClass) {
                $this->transformers[$key] = $replacement;

                break;
            }

            if (is_object($transformer) && $transformer::class === $searchClass) {
                $this->transformers[$key] = $replacement;
            }
        }

        return $this;
    }

    public function watchDirectories(string ...$directories): self
    {
        array_push($this->directoriesToWatch, ...$directories);

        return $this;
    }

    public function writer(Writer $writer): self
    {
        $this->writer = $writer;

        return $this;
    }

    public function formatter(Formatter|string $formatter): self
    {
        $this->formatter = $formatter;

        return $this;
    }

    public function providedVisitorHook(
        VisitorClosure|Closure $visitor,
        ?array $allowedNodes = null,
        VisitorClosureType $type = VisitorClosureType::Before
    ): self {
        if (! $visitor instanceof VisitorClosure) {
            $visitor = new VisitorClosure($visitor, $allowedNodes, $type);
        }

        $this->providedVisitorClosures[] = $visitor;

        return $this;
    }

    public function connectedVisitorHook(
        VisitorClosure|Closure $visitor,
        ?array $allowedNodes = null,
        VisitorClosureType $type = VisitorClosureType::Before
    ): self {
        if (! $visitor instanceof VisitorClosure) {
            $visitor = new VisitorClosure($visitor, $allowedNodes, $type);
        }

        $this->connectedVisitorClosures[] = $visitor;

        return $this;
    }

    public function replaceType(
        string $search,
        TypeScriptNode|string|Closure $replacement
    ): self {
        if ($replacement instanceof TypeScriptNode) {
            $this->typeReplacements[$search] = $replacement;

            return $this;
        }

        if (is_string($replacement)) {
            try {
                $node = ParseUserDefinedTypeAction::instance()->execute($replacement);

                if ($node instanceof TypeScriptUnknown) {
                    $node = new TypeScriptRaw($replacement);
                }

                $this->typeReplacements[$search] = $node;
            } catch (Throwable $e) {
                $this->typeReplacements[$search] = new TypeScriptRaw($replacement);
            }

            return $this;
        }

        if (! $replacement instanceof Closure) {
            throw new Exception('Replacement must be a TypeScriptNode, a string or a Closure');
        }

        $this->typeReplacements[$search] = $replacement;

        return $this;
    }

    public function extension(
        TypeScriptTransformerExtension ...$extensions
    ): self {
        foreach ($extensions as $extension) {
            if (array_key_exists($extension::class, $this->extensions)) {
                continue;
            }

            $this->extensions[$extension::class] = $extension;

            $extension->enrich($this);
        }

        return $this;
    }

    public function get(): TypeScriptTransformerConfig
    {
        $this->ensureConfigIsValid();

        $typeProviders = array_map(
            fn (TypesProvider|string $typeProvider) => is_string($typeProvider) ? new $typeProvider() : $typeProvider,
            $this->typeProviders
        );

        $writer = $this->writer ?? new NamespaceWriter(__DIR__.'/js/typed.ts');

        if (is_string($writer)) {
            $writer = new $writer();
        }

        $formatter = is_string($this->formatter) ? new $this->formatter() : $this->formatter;

        if ($this->typeReplacements) {
            array_unshift($this->providedVisitorClosures, new ReplaceTypesVisitorClosure($this->typeReplacements));
        }

        $transformers = array_map(
            fn (Transformer|string $transformer) => is_string($transformer) ? new $transformer() : $transformer,
            $this->transformers
        );

        if (! empty($transformers)) {
            $typeProviders[] = new TransformerTypesProvider($transformers, $this->directoriesToWatch);
        }

        return new TypeScriptTransformerConfig(
            $typeProviders,
            $writer,
            $formatter,
            $this->directoriesToWatch,
            $this->providedVisitorClosures,
            $this->connectedVisitorClosures,
            $transformers,
        );
    }

    protected function ensureConfigIsValid(): void
    {
        if (! empty($this->transformers) && empty($this->directoriesToWatch)) {
            throw new \Exception('When using transformers, you must specify which directories to watch');
        }
    }
}
