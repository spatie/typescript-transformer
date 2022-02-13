<?php

namespace Spatie\TypeScriptTransformer\Output;

use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

abstract class Output
{
    protected string|array $output;
    protected TypeScriptTransformerConfig $config;
    protected string $path;

    abstract public function __construct(TypeScriptTransformerConfig $config);

    abstract public function append(string $typeScript, string $classPath = null): void;

    abstract public function writeOut(string $fileType): array;

    abstract public function writesMultipleFiles(): bool;

    protected function ensureFilesExist(string $fullPath): void
    {
        if (! file_exists(pathinfo($fullPath, PATHINFO_DIRNAME))) {
            mkdir(pathinfo($fullPath, PATHINFO_DIRNAME), 0755, true);
        }
    }
}
