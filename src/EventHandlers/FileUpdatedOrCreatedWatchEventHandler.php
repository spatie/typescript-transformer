<?php

namespace Spatie\TypeScriptTransformer\EventHandlers;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Data\WatchEventResult;
use Spatie\TypeScriptTransformer\Events\FileCreatedWatchEvent;
use Spatie\TypeScriptTransformer\Events\FileUpdatedWatchEvent;
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

    public function handle($event): ?WatchEventResult
    {
        try {
            $classNode = $this->typeScriptTransformer->loadPhpClassNodeAction->execute($event->path);

            if ($classNode === null) {
                return null;
            }

            if ($this->checkIfClassNodeIsInvalid($event->path, $classNode)) {
                /**
                 * PHPStorm and probably other IDEs, will during refactoring when changing the class name
                 * create a new file with the new class name. Yet the contents will still contain the
                 * old class name. Later on an update event will be triggered with the correct
                 * class name. In order to not generate false positives, ignore a file when
                 * it is not matching the expected class name.
                 */

                return null;
            }

            $newlyTransformed = $this->typeScriptTransformer->transformTypesAction->transformClassNode(
                $this->typeScriptTransformer->config->transformers,
                $classNode
            );
        } catch (Throwable $throwable) {
            if (str_starts_with($throwable::class, 'Roave\BetterReflection')) {
                return null;
            }

            throw $throwable;
        }

        $originalTransformed = $this->transformedCollection->findTransformedByFile(
            $event->path
        );

        if ($originalTransformed && $newlyTransformed === null) {
            $this->transformedCollection->remove($originalTransformed->reference);

            $this->transformedCollection->requireCompleteRewrite();

            return null;
        }

        if ($originalTransformed && $originalTransformed->equals($newlyTransformed)) {
            return null;
        }

        if ($originalTransformed !== null) {
            $this->transformedCollection->remove($originalTransformed->reference);
        }

        if ($newlyTransformed) {
            $this->transformedCollection->add($newlyTransformed);
        }

        return null;
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
