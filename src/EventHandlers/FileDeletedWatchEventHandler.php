<?php

namespace Spatie\TypeScriptTransformer\EventHandlers;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Events\FileDeletedWatchEvent;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;

/**
 * @implements  WatchEventHandler<FileDeletedWatchEvent>
 */
class FileDeletedWatchEventHandler implements WatchEventHandler
{
    public function __construct(
        protected TypeScriptTransformer $typeScriptTransformer,
        protected TransformedCollection $transformedCollection,
    ) {
    }

    public function handle($event): int
    {
        $this->typeScriptTransformer->logger->debug($event->path, 'File Deleted');

        $transformed = $this->transformedCollection->findTransformedByFile($event->path);

        if ($transformed === null) {
            return 0;
        }

        $this->transformedCollection->remove($transformed->reference);

        return 0;
    }
}
