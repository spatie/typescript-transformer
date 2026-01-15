<?php

namespace Spatie\TypeScriptTransformer\EventHandlers;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Data\WatchEventResult;
use Spatie\TypeScriptTransformer\Events\DirectoryDeletedWatchEvent;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;

/**
 * @implements WatchEventHandler<DirectoryDeletedWatchEvent>
 */
class DirectoryDeletedWatchEventHandler implements WatchEventHandler
{
    public function __construct(
        protected TypeScriptTransformer $typeScriptTransformer,
        protected TransformedCollection $transformedCollection,
    ) {
    }

    public function handle($event): ?WatchEventResult
    {
        $this->typeScriptTransformer->logger->debug($event->path, 'Directory Deleted');

        $transformedItems = $this->transformedCollection->findTransformedByDirectory($event->path);

        foreach ($transformedItems as $transformed) {
            $this->transformedCollection->remove($transformed->reference);
        }

        return null;
    }
}
