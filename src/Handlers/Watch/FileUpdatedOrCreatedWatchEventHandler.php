<?php

namespace Spatie\TypeScriptTransformer\Handlers\Watch;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Events\Watch\FileUpdatedWatchEvent;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;
use Throwable;

class FileUpdatedOrCreatedWatchEventHandler implements WatchEventHandler
{
    public function __construct(
        protected TypeScriptTransformer $typeScriptTransformer,
        protected TransformedCollection $transformedCollection,
    ) {
    }

    /**
     * @param FileUpdatedWatchEvent $event
     *
     * @return void
     */
    public function handle($event): void
    {
        try {
            $classNode = $this->typeScriptTransformer->loadPhpClassNodeAction->execute($event->path);

            if ($classNode === null) {
                $this->typeScriptTransformer->log->warning("Multiple class nodes found in {$event->path}");

                return;
            }

            $newlyTransformed = $this->typeScriptTransformer->transformTypesAction->transformClassNode(
                $this->typeScriptTransformer->config->transformers,
                $classNode
            );
        } catch (Throwable $throwable) {
            if (str_starts_with($throwable::class, 'Roave\BetterReflection')) {
                return;
            }

            throw $throwable;
        }

        $originalTransformed = $this->transformedCollection->findTransformedByPath(
            $event->path
        );

        if ($originalTransformed && $newlyTransformed === null) {
            $this->transformedCollection->remove($originalTransformed->reference);
            // TODO: when removing a ts transformed structure (e.g. remove the TypeScript Attributes)
            // everything is correctly removed from the collection
            // but since there are no changes, no new rewrite is triggered
            // somehow we should be able to trigger rewrites based upon namespaces
        }

        if ($newlyTransformed === null) {
            $this->typeScriptTransformer->log->warning("Could not transform {$event->path}");

            return;
        }

        // TODO: at the moment we replace the node when we see an update
        // it could be that no changes are actually made
        // and such a case nothing should be updated

        if ($originalTransformed !== null) {
            $this->transformedCollection->remove($originalTransformed->reference);
        }

        $this->transformedCollection->add($newlyTransformed);
    }
}
