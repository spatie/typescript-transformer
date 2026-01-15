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
        public array $createdFiles = [],
        public array $updatedFiles = [],
        public array $deletedFiles = [],
        public array $deletedDirectories = [],
    ) {

    }

    public function handleWatchEvent(WatchEvent $event): void
    {
        if ($event instanceof FileCreatedWatchEvent) {
            $this->createdFiles[] = $event->path;
        }

        if ($event instanceof FileUpdatedWatchEvent) {
            $this->updatedFiles[] = $event->path;
        }

        if ($event instanceof FileDeletedWatchEvent) {
            $this->deletedFiles[] = $event->path;
        }

        if ($event instanceof DirectoryDeletedWatchEvent) {
            $this->deletedDirectories[] = $event->path;
        }
    }
}
