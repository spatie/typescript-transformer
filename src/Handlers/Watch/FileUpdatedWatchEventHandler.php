<?php

namespace Spatie\TypeScriptTransformer\Handlers\Watch;

use Spatie\TypeScriptTransformer\Collections\ReferenceMap;
use Spatie\TypeScriptTransformer\Events\Watch\FileUpdatedWatchEvent;
use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;

class FileUpdatedWatchEventHandler implements WatchEventHandler
{
    public function __construct(
        protected TypeScriptTransformer $typeScriptTransformer,
        protected TransformedCollection $transformedCollection,
        protected ReferenceMap $referenceMap
    ) {
    }

    /**
     * @param FileUpdatedWatchEvent $event
     *
     * @return void
     */
    public function handle($event): void
    {
        $classNode = $this->typeScriptTransformer->loadPhpClassNodeAction->execute($event->path);

        if ($classNode === null) {
            $this->typeScriptTransformer->log->warning("Multiple class nodes found in {$event->path}");

            return;
        }

        $newlyTransformed = $this->typeScriptTransformer->transformTypesAction->transformClassNode(
            $this->typeScriptTransformer->config->transformers,
            $classNode
        );

        if ($newlyTransformed === null) {
            $this->typeScriptTransformer->log->warning("Could not transform {$event->path}");

            return;
        }

        // TODO: at the moment we replace the node when we see an update
        // it could be that no changes are actually made
        // and such a case nothing should be updated
        $originalTransformed = $this->transformedCollection->findTransformedByPath(
            $event->path
        );

        if ($originalTransformed === null) {
            $this->addNewlyTransformed($newlyTransformed);

            return;
        }

        foreach ($originalTransformed->referencedBy as $referencedBy => $key) {
            /** @var Transformed $referencedBy */
            $referencedBy->markReferenceRemoved($originalTransformed);
            $referencedBy->markAsChanged();
        }

        $this->referenceMap->remove($originalTransformed);
        $this->transformedCollection->removeTransformedByPath($event->path);

        $this->addNewlyTransformed($newlyTransformed);
    }

    protected function addNewlyTransformed(Transformed $transformed): void
    {
        $this->transformedCollection->add($transformed);

        $this->typeScriptTransformer->executeProvidedClosuresAction->execute([$transformed]);
        $this->typeScriptTransformer->connectReferencesAction->execute([$transformed]);
        $this->typeScriptTransformer->executeConnectedClosuresAction->execute([$transformed]);
    }
}
