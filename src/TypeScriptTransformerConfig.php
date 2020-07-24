<?php

namespace Spatie\TypescriptTransformer;

use Spatie\TypescriptTransformer\Collectors\AnnotationCollector;

class TypeScriptTransformerConfig
{
    protected string $searchingPath;

    protected array $transformers = [];

    protected array $collectors;

    protected string $outputFile = 'types.d.ts';

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

    public function getSearchingPath(): string
    {
        return $this->searchingPath;
    }

    /**
     * @return \Spatie\TypescriptTransformer\Transformers\Transformer[]
     */
    public function getTransformers(): array
    {
        return array_map(
            fn (string $transformer) => new $transformer,
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
            fn (string $collector) => new $collector($this),
            $this->collectors
        );
    }
}
