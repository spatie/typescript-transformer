<?php

namespace Spatie\TypeScriptTransformer\Handlers\Watch;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Events\Watch\DirectoryDeletedWatchEvent;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;

class DirectoryDeletedWatchEventHandler implements WatchEventHandler
{
    public function __construct(
        protected TypeScriptTransformer $typeScriptTransformer,
        protected TransformedCollection $transformedCollection,
    ) {
    }

    /**
     * @param DirectoryDeletedWatchEvent $event
     */
    public function handle($event): void
    {
        $transformedItems = $this->transformedCollection->findTransformedByDirectory($event->path);

        foreach ($transformedItems as $transformed) {
            $this->transformedCollection->remove($transformed->reference);
        }
    }
}
