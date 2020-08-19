<?php

namespace Spatie\TypescriptTransformer;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use Spatie\TypescriptTransformer\Collectors\AnnotationCollector;
use Spatie\TypescriptTransformer\Exceptions\InvalidClassPropertyReplacer;
use Spatie\TypescriptTransformer\Support\TransformerFactory;

class TypeScriptTransformerConfig
{
    protected string $searchingPath;

    protected array $transformers = [];

    protected array $collectors;

    protected string $outputFile = 'types.d.ts';

    private array $classPropertyReplacements = [];

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

    /**
     * @return \Spatie\TypescriptTransformer\Transformers\Transformer[]
     */
    public function getTransformers(): array
    {
        $factory = new TransformerFactory($this);

        return array_map(
            fn(string $transformer) => $factory->create($transformer),
            $this->transformers
        );
    }

    public function getOutputFile(): string
    {
        return $this->outputFile;
    }

    /**
     * @return \Spatie\TypescriptTransformer\Collectors\Collector[]
     */
    public function getCollectors(): array
    {
        return array_map(
            fn(string $collector) => new $collector($this),
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
