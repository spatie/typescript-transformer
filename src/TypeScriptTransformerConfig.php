<?php

namespace Spatie\TypescriptTransformer;

class TypeScriptTransformerConfig
{
    private string $searchingPath;

    private array $transformers;

    private string $default_file;

    private string $output_path;

    public function __construct(
        string $searchingPath,
        array $transformers,
        string $default_file,
        string $output_path
    ) {
        $this->searchingPath = $searchingPath;
        $this->transformers = $transformers;
        $this->default_file = $default_file;
        $this->output_path = $output_path;
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
        return $this->default_file;
    }

    public function getOutputPath(): string
    {
        return $this->output_path;
    }
}
