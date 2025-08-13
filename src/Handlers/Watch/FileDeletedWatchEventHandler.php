<?php

namespace Spatie\TypeScriptTransformer\Handlers\Watch;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Events\Watch\FileDeletedWatchEvent;
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

    public function handle($event): void
    {
        $this->typeScriptTransformer->log->debug($event->path, 'File Deleted');

        $transformed = $this->transformedCollection->findTransformedByFile($event->path);

        if ($transformed === null) {
            return;
        }

        $this->transformedCollection->remove($transformed->reference);
    }
}
