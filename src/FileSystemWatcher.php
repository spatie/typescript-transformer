<?php

namespace Spatie\TypeScriptTransformer;

use Exception;
use Spatie\TypeScriptTransformer\Collections\ReferenceMap;
use Spatie\TypeScriptTransformer\Events\Watch\DirectoryDeletedWatchEvent;
use Spatie\TypeScriptTransformer\Events\Watch\FileCreatedWatchEvent;
use Spatie\TypeScriptTransformer\Events\Watch\FileDeletedWatchEvent;
use Spatie\TypeScriptTransformer\Events\Watch\FileUpdatedWatchEvent;
use Spatie\TypeScriptTransformer\Events\Watch\WatchEvent;
use Spatie\TypeScriptTransformer\Handlers\Watch\FileCreatedWatchEventHandler;
use Spatie\TypeScriptTransformer\Handlers\Watch\FileDeletedWatchEventHandler;
use Spatie\TypeScriptTransformer\Handlers\Watch\FileUpdatedWatchEventHandler;
use Spatie\TypeScriptTransformer\Handlers\Watch\WatchEventHandler;
use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\Watcher\Exceptions\CouldNotStartWatcher;
use Spatie\Watcher\Watch;

class FileSystemWatcher
{
    protected array $eventsBuffer = [];

    protected bool $processing = false;

    /** @var array<class-string<WatchEvent>, WatchEventHandler> */
    protected array $handlers = [];

    public function __construct(
        protected TypeScriptTransformer $typeScriptTransformer,
        protected TransformedCollection $transformedCollection,
        protected ReferenceMap $referenceMap,
    ) {
        $this->initializeHandlers();
    }

    public function run(): void
    {
        $watcher = Watch::paths($this->typeScriptTransformer->config->directoriesToWatch)
            ->onFileCreated(function (string $path) {
                if (! str_ends_with($path, '.php')) {
                    return;
                }

                $this->eventsBuffer[] = new FileCreatedWatchEvent($path);
            })
            ->onfileUpdated(function (string $path) {
                if (! str_ends_with($path, '.php')) {
                    return;
                }

                $this->eventsBuffer[] = new FileUpdatedWatchEvent($path);
            })
            ->onFileDeleted(function (string $path) {
                if (! str_ends_with($path, '.php')) {
                    return;
                }

                $this->eventsBuffer[] = new FileDeletedWatchEvent($path);
            })
            ->onDirectoryDeleted(function (string $path) {
                $this->eventsBuffer[] = new DirectoryDeletedWatchEvent($path);
            })
            ->shouldContinue(function () {
                // TODO: we probably want a better implementation than this but it works
                if (count($this->eventsBuffer) > 0 && $this->processing === false) {
                    $this->processing = true;
                    $this->processBuffer();
                    $this->processing = false;
                }

                return true;
            });

        try {
            $this->typeScriptTransformer->log->info('Starting watcher');

            $watcher->start();
        } catch (CouldNotStartWatcher $e) {
            throw new Exception(
                'Could not start watcher. Make sure you have required chokidar. (https://github.com/spatie/file-system-watcher?tab=readme-ov-file#installation)'
            );
        }
    }

    protected function initializeHandlers(): void
    {
        // TODO: handle directory deleted

        $this->handlers[FileCreatedWatchEvent::class] = new FileCreatedWatchEventHandler(
            $this->typeScriptTransformer,
            $this->transformedCollection,
            $this->referenceMap
        );

        $this->handlers[FileUpdatedWatchEvent::class] = new FileUpdatedWatchEventHandler(
            $this->typeScriptTransformer,
            $this->transformedCollection,
            $this->referenceMap
        );

        $this->handlers[FileDeletedWatchEvent::class] = new FileDeletedWatchEventHandler(
            $this->typeScriptTransformer,
            $this->transformedCollection,
            $this->referenceMap
        );
    }

    protected function processBuffer(): void
    {
        $this->typeScriptTransformer->log->info('Processing events');

        [$events, $this->eventsBuffer] = [$this->eventsBuffer, []];

        foreach ($events as $event) {
            $this->handlers[$event::class]->handle($event);
        }

        $this->tryToConnectMissingReferencesWithNewTransformed();

        $this->typeScriptTransformer->outputTransformed(
            $this->transformedCollection,
            $this->referenceMap
        );

        $this->typeScriptTransformer->log->info('Processed events');
    }

    protected function tryToConnectMissingReferencesWithNewTransformed(): void
    {
        foreach ($this->transformedCollection as $transformed) {
            foreach ($transformed->missingReferences as $missingReference => $typeReferences) {
                $referenced = $this->referenceMap->getByReferenceKey($missingReference);

                if ($referenced === null) {
                    continue;
                }

                $referenced->markMissingReferenceFound($transformed);
                $referenced->markAsChanged();

                $transformed->referencedBy[$referenced] = $referenced->reference->getKey();

                break;
            }
        }
    }
}
