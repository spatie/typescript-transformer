<?php

namespace Spatie\TypescriptTransformer;

use Spatie\TypescriptTransformer\Exceptions\InvalidConfig;

class TypeScriptTransformerConfig
{
    private ?string $searchingPath = null;

    private array $transformers = [];

    private array $collectors = [];

    private string $outputFile = 'types.d.ts';

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
        $this->collectors = $collectors;

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

    public function ensureConfigIsValid()
    {
        if (empty($this->searchingPath)) {
            throw InvalidConfig::missingSearchingPath();
        }

        if (empty($this->outputFile)) {
            throw InvalidConfig::missingOutputFile();
        }
    }
}
