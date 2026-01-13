<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Support\WriteableFile;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class FormatFilesAction
{
    public function __construct(
        public TypeScriptTransformerConfig $config,
    ) {
    }

    /**
     * @param array<WriteableFile> $writeableFiles
     */
    public function execute(array $writeableFiles): void
    {
        if ($this->config->formatter === null) {
            return;
        }

        $filePaths = [];

        foreach ($writeableFiles as $writeableFile) {
            if (! $writeableFile->changed) {
                continue;
            }

            $filePaths[] = $this->config->outputDirectory.DIRECTORY_SEPARATOR.$writeableFile->path;
        }

        if (count($filePaths) > 0) {
            $this->config->formatter->format($filePaths);
        }
    }
}
