<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Support\WriteableFile;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class WriteFilesAction
{
    public function __construct(
        public TypeScriptTransformerConfig $config,
    ) {
    }

    /** @param  array<WriteableFile>  $writeableFiles */
    public function execute(
        array $writeableFiles
    ): void {
        foreach ($writeableFiles as $writeableFile) {
            $this->writeFile($writeableFile);
        }
    }

    protected function writeFile(WriteableFile $file): void
    {
        $directory = dirname($file->path);

        if (is_dir($directory) === false) {
            mkdir($directory, recursive: true);
        }

        file_put_contents($file->path, $file->contents);
    }
}
