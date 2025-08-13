<?php

namespace Spatie\TypeScriptTransformer\Handlers\Watch;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Events\Watch\FileCreatedWatchEvent;
use Spatie\TypeScriptTransformer\Events\Watch\FileUpdatedWatchEvent;
use Spatie\TypeScriptTransformer\Events\Watch\WatchEvent;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;
use Throwable;

/**
 * @implements WatchEventHandler<FileUpdatedWatchEvent|FileCreatedWatchEvent>
 */
class FileUpdatedOrCreatedWatchEventHandler implements WatchEventHandler
{
    public function __construct(
        protected TypeScriptTransformer $typeScriptTransformer,
        protected TransformedCollection $transformedCollection,
    ) {
    }

    /**
     * @param WatchEvent $event
     *
     * @return void
     */
    public function handle($event): void
    {
        $this->typeScriptTransformer->log->debug(
            $event->path,
            $event instanceof FileCreatedWatchEvent ? 'File Created' : 'File Updated',
        );

        try {
            $classNode = $this->typeScriptTransformer->loadPhpClassNodeAction->execute($event->path);

            if ($classNode === null) {
                $this->typeScriptTransformer->log->warning("Multiple class nodes found in {$event->path}");

                return;
            }

            if ($this->checkIfClassNodeIsInvalid($event->path, $classNode)) {
                /**
                 * PHPStorm and probably other IDEs, will during refactoring when changing the class name
                 * create a new file with the new class name. Yet the contents will still contain the
                 * old class name. Later on an update event will be triggered with the correct
                 * class name. In order to not generate false positives, ignore a file when
                 * it is not matching the expected class name.
                 */

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

        $originalTransformed = $this->transformedCollection->findTransformedByFile(
            $event->path
        );

        if ($originalTransformed && $newlyTransformed === null) {
            $this->transformedCollection->remove($originalTransformed->reference);

            $this->transformedCollection->requireCompleteRewrite();

            return;
        }

        if ($originalTransformed && $newlyTransformed) {
            // TODO: at the moment we replace the node when we see an update
            // it could be that no changes are actually made
            // and such a case nothing should be updated
            // Ideally we check if two transformed items correspond
        }

        if ($originalTransformed !== null) {
            $this->transformedCollection->remove($originalTransformed->reference);
        }

        if ($newlyTransformed) {
            $this->transformedCollection->add($newlyTransformed);
        }
    }

    protected function checkIfClassNodeIsInvalid(
        string $path,
        PhpClassNode $classNode,
    ): bool {
        $expectedClassName = basename($path, '.php');
        $actualClassName = $classNode->getShortName();

        return $expectedClassName !== $actualClassName;
    }
}
