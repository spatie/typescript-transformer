<?php

namespace Spatie\TypeScriptTransformer;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use Spatie\TypeScriptTransformer\Collectors\AnnotationCollector;
use Spatie\TypeScriptTransformer\Writers\TypeDefinitionWriter;
use Spatie\TypeScriptTransformer\Writers\Writer;
use Spatie\TypeScriptTransformer\Exceptions\InvalidDefaultTypeReplacer;

class TypeScriptTransformerConfig
{
    private string $searchingPath;

    private array $transformers = [];

    private array $collectors;

    private string $outputFile = 'types.d.ts';

    private array $defaultTypeReplacements = [];

    private string $writer = TypeDefinitionWriter::class;

    private bool $enableFormatting = false;

    public function __construct()
    {
        $this->collectors = [AnnotationCollector::class];
    }

    public static function create(): self
    {
        return new self();
    }

    public function searchingPath(string $searchingPath): self
    {
        $this->searchingPath = $searchingPath;

        return $this;
    }

    public function transformers(array $transformers): self
    {
        $this->transformers = $transformers;

        return $this;
    }

    public function collectors(array $collectors)
    {
        $this->collectors = array_merge($collectors, [AnnotationCollector::class]);

        return $this;
    }

    public function writer(string $writer): self
    {
        $this->writer = $writer;

        return $this;
    }

    public function outputFile(string $defaultFile): self
    {
        $this->outputFile = $defaultFile;

        return $this;
    }

    public function defaultTypeReplacements(array $defaultTypeReplacements): self
    {
        $this->defaultTypeReplacements = $defaultTypeReplacements;

        return $this;
    }

    public function enableFormatting(bool $enableFormatting = true): self
    {
        $this->enableFormatting = $enableFormatting;

        return $this;
    }

    public function getSearchingPath(): string
    {
        return $this->searchingPath;
    }

    /**@return \Spatie\TypeScriptTransformer\Transformers\Transformer[] */
    public function getTransformers(): array
    {
        $factory = new TransformerFactory($this);

        return array_map(
            fn (string $transformer) => $factory->create($transformer),
            $this->transformers
        );
    }

    public function getWriter(): Writer
    {
        return new $this->writer;
    }

    public function getOutputFile(): string
    {
        return $this->outputFile;
    }

    /** @return \Spatie\TypeScriptTransformer\Collectors\Collector[] */
    public function getCollectors(): array
    {
        return array_map(
            fn (string $collector) => new $collector($this),
            $this->collectors
        );
    }

    public function getDefaultTypeReplacements(): array
    {
        $typeResolver = new TypeResolver();

        $replacements = [];

        foreach ($this->defaultTypeReplacements as $class => $replacement) {
            if (! class_exists($class)) {
                throw InvalidDefaultTypeReplacer::classDoesNotExist($class);
            }

            $replacements[$class] = $replacement instanceof Type
                ? $replacement
                : $typeResolver->resolve($replacement);
        }

        return $replacements;
    }

    public function isFormattingEnabled(): bool
    {
        return $this->enableFormatting;
    }
}
