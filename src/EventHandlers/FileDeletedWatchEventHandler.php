<?php

namespace Spatie\TypeScriptTransformer\EventHandlers;

use Spatie\TypeScriptTransformer\Collections\PhpNodeCollection;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Data\WatchEventResult;
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
        protected PhpNodeCollection $phpNodeCollection,
    ) {
    }

    public function handle($event): ?WatchEventResult
    {
        $this->typeScriptTransformer->logger->debug($event->path, 'File Deleted');

        $this->phpNodeCollection->removeByFile($event->path);

        $transformed = $this->transformedCollection->findTransformedByFile($event->path);

        if ($transformed === null) {
            return null;
        }

        $this->transformedCollection->remove($transformed->reference);

        return null;
    }
}
