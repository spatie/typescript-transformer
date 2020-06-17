<?php

namespace Spatie\TypescriptTransformer;

use Spatie\TypescriptTransformer\Exceptions\InvalidConfig;

class TypeScriptTransformerConfig
{
    private ?string $searchingPath = null;

    private array $transformers = [];

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

    public function outputFile(string $defaultFile): self
    {
        $this->outputFile = $defaultFile;

        return $this;
    }

    public function getSearchingPath(): string
    {
        return $this->searchingPath;
    }

    public function getTransformers(): array
    {
        return $this->transformers;
    }

    public function getOutputFile(): string
    {
        return $this->outputFile;
    }

    public function ensureConfigIsValid()
    {
        if (empty($this->searchingPath)) {
            throw InvalidConfig::missingSearchingPath();
        }

        if (empty($this->outputFile)) {
            throw InvalidConfig::missingOutputFile();
        }

        if (count($this->transformers) === 0) {
            throw InvalidConfig::missingTransformers();
        }
    }
}
