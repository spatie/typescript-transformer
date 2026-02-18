<?php

namespace Spatie\TypeScriptTransformer;

use Closure;
use Exception;
use Spatie\TypeScriptTransformer\Actions\ParseUserDefinedTypeAction;
use Spatie\TypeScriptTransformer\Formatters\Formatter;
use Spatie\TypeScriptTransformer\Support\Extensions\TypeScriptTransformerExtension;
use Spatie\TypeScriptTransformer\TransformedProviders\TransformedProvider;
use Spatie\TypeScriptTransformer\TransformedProviders\TransformerProvider;
use Spatie\TypeScriptTransformer\TransformedProviders\WatchingTransformedProvider;
use Spatie\TypeScriptTransformer\Transformers\Transformer;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptRaw;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptUnknown;
use Spatie\TypeScriptTransformer\Visitor\Common\ReplaceTypesVisitorClosure;
use Spatie\TypeScriptTransformer\Visitor\VisitorClosure;
use Spatie\TypeScriptTransformer\Visitor\VisitorClosureType;
use Spatie\TypeScriptTransformer\Writers\GlobalNamespaceWriter;
use Spatie\TypeScriptTransformer\Writers\Writer;
use Throwable;

class TypeScriptTransformerConfigFactory
{
    /**
     * @param array<TransformedProvider|string> $transformedProviders
     * @param array<Transformer|string> $transformers
     * @param array<string> $directoriesToTransform
     * @param array<class-string|string, TypeScriptNode> $typeReplacements
     * @param array<class-string<TypeScriptTransformerExtension>, TypeScriptTransformerExtension> $extensions
     * @param array<VisitorClosure> $providedVisitorClosures
     * @param array<VisitorClosure> $connectedVisitorClosures
     * @param array<string> $configPaths
     */
    public function __construct(
        protected string $outputDirectory = __DIR__.'/generated',
        protected array $transformedProviders = [],
        protected string|Writer|null $writer = null,
        protected string|Formatter|null $formatter = null,
        protected array $transformers = [],
        protected array $directoriesToTransform = [],
        protected array $typeReplacements = [],
        protected array $extensions = [],
        protected array $providedVisitorClosures = [],
        protected array $connectedVisitorClosures = [],
        protected array $configPaths = [],
    ) {
    }

    public static function create(): self
    {
        return new self();
    }

    public function provider(TransformedProvider|string ...$transformedProviders): self
    {
        foreach ($transformedProviders as $transformedProvider) {
            if ($transformedProvider === TransformerProvider::class || $transformedProvider instanceof TransformerProvider) {
                throw new Exception("Please add transformers using the config's `transformer` method.");
            }
        }

        array_push($this->transformedProviders, ...$transformedProviders);

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

    public function transformDirectories(string ...$directories): self
    {
        array_push($this->directoriesToTransform, ...$directories);

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

    public function configPath(
        string ...$paths
    ): self {
        $this->configPaths = $paths;

        return $this;
    }

    public function outputDirectory(string $directory): self
    {
        $this->outputDirectory = $directory;

        return $this;
    }

    public function get(): TypeScriptTransformerConfig
    {
        $this->ensureConfigIsValid();

        $transformedProviders = array_map(
            fn (TransformedProvider|string $transformedProvider) => is_string($transformedProvider) ? new $transformedProvider() : $transformedProvider,
            $this->transformedProviders
        );

        $directoriesToWatch = [
            ...$this->directoriesToTransform,
            ...$this->configPaths,
        ];

        foreach ($transformedProviders as $transformedProvider) {
            if ($transformedProvider instanceof WatchingTransformedProvider) {
                array_push($directoriesToWatch, ...$transformedProvider->directoriesToWatch());
            }
        }

        $writer = $this->writer ?? new GlobalNamespaceWriter(__DIR__.'/js/typed.ts');

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
            $transformedProviders[] = new TransformerProvider($transformers, $this->directoriesToTransform);
        }

        return new TypeScriptTransformerConfig(
            realpath(rtrim($this->outputDirectory, DIRECTORY_SEPARATOR)) ?: rtrim($this->outputDirectory, DIRECTORY_SEPARATOR),
            $transformedProviders,
            $writer,
            $formatter,
            $directoriesToWatch,
            $this->providedVisitorClosures,
            $this->connectedVisitorClosures,
            $transformers,
            $this->configPaths
        );
    }

    protected function ensureConfigIsValid(): void
    {
        if (! empty($this->transformers) && empty($this->directoriesToTransform)) {
            throw new \Exception('When using transformers, you must specify which directories to watch');
        }
    }
}
