<?php

namespace Spatie\TypeScriptTransformer\Handlers\Watch;

use Spatie\TypeScriptTransformer\Collections\ReferenceMap;
use Spatie\TypeScriptTransformer\Events\Watch\FileDeletedWatchEvent;
use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;

class FileDeletedWatchEventHandler implements WatchEventHandler
{
    public function __construct(
        protected TypeScriptTransformer $typeScriptTransformer,
        protected TransformedCollection $transformedCollection,
        protected ReferenceMap $referenceMap
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

        foreach ($transformed->referencedBy as $referencedBy => $key) {
            /** @var Transformed $referencedBy */
            $referencedBy->markReferenceRemoved($transformed);
            $referencedBy->markAsChanged();
        }

        $this->referenceMap->remove($transformed);
        $this->transformedCollection->removeTransformedByPath($event->path);
    }
}
