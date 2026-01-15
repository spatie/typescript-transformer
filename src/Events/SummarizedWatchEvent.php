<?php

namespace Spatie\TypeScriptTransformer\Events;

class SummarizedWatchEvent extends WatchEvent
{
    /**
     * @param array<string> $createdFiles
     * @param array<string> $updatedFiles
     * @param array<string> $deletedFiles
     * @param array<string> $deletedDirectories
     */
    public function __construct(
        public array $createdFiles,
        public array $updatedFiles,
        public array $deletedFiles,
        public array $deletedDirectories,
    ) {

    }
}
