<?php

namespace Spatie\TypeScriptTransformer;

use Spatie\TypeScriptTransformer\Formatters\Formatter;
use Spatie\TypeScriptTransformer\Transformers\Transformer;
use Spatie\TypeScriptTransformer\TypeProviders\TransformerTypesProvider;
use Spatie\TypeScriptTransformer\TypeProviders\TypesProvider;
use Spatie\TypeScriptTransformer\Writers\NamespaceWriter;
use Spatie\TypeScriptTransformer\Writers\Writer;

class TypeScriptTransformerConfigBuilder
{
    /**
     * @param array<TypesProvider|string> $typeProviders
     * @param array<Transformer|string> $transformers
     */
    public function __construct(
        protected array $typeProviders = [],
        protected string|Writer|null $writer = null,
        protected string|Formatter|null $formatter = null,
        protected array $transformers = [],
        protected array $directoriesToWatch = [],
    ) {
    }

    public function typesProvider(TypesProvider|string ...$typesProvider): self
    {
        array_push($this->typeProviders, ...$typesProvider);

        return $this;
    }

    public function transformer(string|Transformer ...$transformer): self
    {
        array_push($this->transformers, ...$transformer);

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

    public function get(): TypeScriptTransformerConfig
    {
        $this->ensureConfigIsValid();

        $typeProviders = array_map(
            fn (TypesProvider|string $typeProvider) => is_string($typeProvider) ? new $typeProvider : $typeProvider,
            $this->typeProviders
        );

        if (! empty($this->transformers)) {
            $transformers = array_map(
                fn (Transformer|string $transformer) => is_string($transformer) ? new $transformer : $transformer,
                $this->transformers
            );

            $typeProviders[] = new TransformerTypesProvider($transformers, $this->directoriesToWatch);
        }

        $writer = $this->writer ?? new NamespaceWriter(
            resource_path('types/generated.d.ts')
        );

        if (is_string($writer)) {
            $writer = new $writer;
        }

        $formatter = is_string($this->formatter) ? new $this->formatter : $this->formatter;

        return new TypeScriptTransformerConfig(
            $typeProviders,
            $writer,
            $formatter,
            $this->directoriesToWatch
        );
    }

    protected function ensureConfigIsValid(): void
    {
        if (! empty($this->transformers) && empty($this->directoriesToWatch)) {
            throw new \Exception('When using transformers, you must specify which directories to watch');
        }
    }
}
