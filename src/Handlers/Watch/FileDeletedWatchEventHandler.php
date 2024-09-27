<?php

namespace Spatie\TypeScriptTransformer\Handlers\Watch;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Events\Watch\FileDeletedWatchEvent;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;

class FileDeletedWatchEventHandler implements WatchEventHandler
{
    public function __construct(
        protected TypeScriptTransformer $typeScriptTransformer,
        protected TransformedCollection $transformedCollection,
    ) {
    }

    /**
     * @param FileDeletedWatchEvent $event
     */
    public function handle($event): void
    {
        $transformed = $this->transformedCollection->findTransformedByPath($event->path);

        if ($transformed === null) {
            return;
        }

        $this->transformedCollection->remove($transformed->reference);
    }
}
