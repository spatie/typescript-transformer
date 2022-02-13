<?php

namespace Spatie\TypeScriptTransformer\Output;

use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class SingleFileOutput extends Output
{

    public function __construct(protected TypeScriptTransformerConfig $config)
    {
        $this->output = '';
        $this->path = $config->getOutputDestination();
    }

    public function append(string $typeScript, string $classPath = null): void
    {
        $this->output .= $typeScript;
    }

    public function writeOut(string $fileType): array
    {
        $fileType = ltrim($fileType, '.');
        $path = preg_replace("/.{$fileType}$/", '', $this->path);
        $path .= ".{$fileType}";
        $this->ensureFilesExist($path);

        file_put_contents(
            $path,
            $this->output
        );

        // We only write to one path
        return [$path];
    }

    public function writesMultipleFiles(): bool
    {
        return false;
    }
}
