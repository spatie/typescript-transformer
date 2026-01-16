<?php

namespace Spatie\TypeScriptTransformer\EventHandlers;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Data\WatchEventResult;
use Spatie\TypeScriptTransformer\Events\DirectoryDeletedWatchEvent;
use Spatie\TypeScriptTransformer\Events\FileCreatedWatchEvent;
use Spatie\TypeScriptTransformer\Events\FileDeletedWatchEvent;
use Spatie\TypeScriptTransformer\Events\FileUpdatedWatchEvent;
use Spatie\TypeScriptTransformer\Events\SummarizedWatchEvent;
use Spatie\TypeScriptTransformer\Events\WatchEvent;
use Spatie\TypeScriptTransformer\TransformedProviders\WatchingTransformedProvider;

class WatchingTransformedProvidersHandler implements WatchEventHandler
{
    public function __construct(
        protected WatchingTransformedProvider $transformedProvider,
        protected TransformedCollection $transformedCollection,
    ) {
    }

    public function handle($event): ?WatchEventResult
    {
        $eventPaths = $this->getPathsFromEvent($event);
        $watchingDirectories = $this->transformedProvider->directoriesToWatch();

        foreach ($eventPaths as $eventPath) {
            foreach ($watchingDirectories as $watchingDirectory) {
                if (str_starts_with($eventPath, $watchingDirectory)) {
                    return $this->transformedProvider->handleWatchEvent($event, $this->transformedCollection);
                }
            }
        }

        return null;
    }

    /** @return array<string> */
    protected function getPathsFromEvent(WatchEvent $event): array
    {
        if ($event instanceof FileCreatedWatchEvent
            || $event instanceof FileUpdatedWatchEvent
            || $event instanceof FileDeletedWatchEvent
            || $event instanceof DirectoryDeletedWatchEvent
        ) {
            return [$event->path];
        }

        if ($event instanceof SummarizedWatchEvent) {
            return [
                ...$event->createdFiles,
                ...$event->updatedFiles,
                ...$event->deletedFiles,
                ...$event->deletedDirectories,
            ];
        }

        return [];
    }
}
