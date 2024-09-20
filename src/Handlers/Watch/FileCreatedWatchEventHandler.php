<?php

namespace Spatie\TypeScriptTransformer\Handlers\Watch;

use Spatie\TypeScriptTransformer\Collections\ReferenceMap;
use Spatie\TypeScriptTransformer\Events\Watch\FileCreatedWatchEvent;
use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;

class FileCreatedWatchEventHandler implements WatchEventHandler
{
    public function __construct(
        protected TypeScriptTransformer $typeScriptTransformer,
        protected TransformedCollection $transformedCollection,
        protected ReferenceMap $referenceMap
    ) {
    }

    /**
     * @param FileCreatedWatchEvent $event
     */
    public function handle($event): void
    {
        $classNode = $this->typeScriptTransformer->loadPhpClassNodeAction->execute($event->path);

        if($classNode === null) {
            $this->typeScriptTransformer->log->warning("Multiple class nodes found in {$event->path}");

            return;
        }

        $transformed = $this->typeScriptTransformer->transformTypesAction->transformClassNode(
            $this->typeScriptTransformer->config->transformers,
            $classNode
        );

        if ($transformed === null) {
            $this->typeScriptTransformer->log->warning("Could not transform {$event->path}");

            return;
        }

        $this->transformedCollection->add($transformed);

        $this->typeScriptTransformer->executeProvidedClosuresAction->execute([$transformed]);
        $this->typeScriptTransformer->connectReferencesAction->execute([$transformed]);
        $this->typeScriptTransformer->executeConnectedClosuresAction->execute([$transformed]);
    }
}
