<?php

namespace Spatie\TypescriptTransformer;

use Spatie\TypescriptTransformer\Exceptions\InvalidConfig;

class TypeScriptTransformerConfig
{
    private ?string $searchingPath = null;

    private array $transformers = [];

    private string $defaultFile = 'types.d.ts';

    private ?string $outputPath = null;

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

    public function defaultFile(string $defaultFile): self
    {
        $this->defaultFile = $defaultFile;

        return $this;
    }

    public function outputPath(string $outputPath): self
    {
        $this->outputPath = $outputPath;

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

    public function getDefaultFile(): string
    {
        return $this->defaultFile;
    }

    public function getOutputPath(): string
    {
        return $this->outputPath;
    }

    public function ensureConfigIsValid()
    {
        if(empty($this->searchingPath)){
            throw InvalidConfig::missingSearchingPath();
        }

        if(empty($this->defaultFile)){
            throw InvalidConfig::missingDefaultFile();
        }

        if(count($this->transformers) === 0){
            throw InvalidConfig::missingTransformers();
        }

        if(empty($this->outputPath)){
            throw InvalidConfig::missingOutputPath();
        }
    }
}
