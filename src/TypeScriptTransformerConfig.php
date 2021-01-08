<?php

namespace Spatie\TypeScriptTransformer;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use Spatie\TypeScriptTransformer\Collectors\AnnotationCollector;
use Spatie\TypeScriptTransformer\Exceptions\InvalidClassPropertyReplacer;
use Spatie\TypeScriptTransformer\OutputFormatters\OutputFormatter;
use Spatie\TypeScriptTransformer\OutputFormatters\TypeDefinitionOutputFormatter;
use Spatie\TypeScriptTransformer\Support\TransformerFactory;

class TypeScriptTransformerConfig
{
    private string $searchingPath;

    private array $transformers = [];

    private array $collectors;

    private string $outputFile = 'types.d.ts';

    private array $classPropertyReplacements = [];

    private string $outputFormatter = TypeDefinitionOutputFormatter::class;

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

    public function outputFormatter(string $outputFormatter): self
    {
        $this->outputFormatter = $outputFormatter;

        return $this;
    }

    public function outputFile(string $defaultFile): self
    {
        $this->outputFile = $defaultFile;

        return $this;
    }

    public function classPropertyReplacements(array $classPropertyReplacements): self
    {
        $this->classPropertyReplacements = $classPropertyReplacements;

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

    public function getOutputFormatter(): OutputFormatter
    {
        return new $this->outputFormatter;
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

    public function getClassPropertyReplacements(): array
    {
        $typeResolver = new TypeResolver();

        $replacements = [];

        foreach ($this->classPropertyReplacements as $class => $replacement) {
            if (! class_exists($class)) {
                throw InvalidClassPropertyReplacer::classDoesNotExist($class);
            }

            $replacements[$class] = $replacement instanceof Type
                ? $replacement
                : $typeResolver->resolve($replacement);
        }

        return $replacements;
    }
}
