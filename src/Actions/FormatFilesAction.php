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
     * @param  array<WriteableFile>  $writtenFiles
     */
    public function execute(array $writtenFiles): void
    {
        if ($this->config->formatter === null) {
            return;
        }

        $this->config->formatter->format(
            array_map(fn (WriteableFile $writtenFile) => $this->config->outputDirectory.DIRECTORY_SEPARATOR.$writtenFile->path, $writtenFiles)
        );
    }
}
