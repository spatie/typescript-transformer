<?php

namespace Spatie\TypeScriptTransformer;

use Exception;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Data\WatchEventResult;
use Spatie\TypeScriptTransformer\Events\Watch\DirectoryDeletedWatchEvent;
use Spatie\TypeScriptTransformer\Events\Watch\FileCreatedWatchEvent;
use Spatie\TypeScriptTransformer\Events\Watch\FileDeletedWatchEvent;
use Spatie\TypeScriptTransformer\Events\Watch\FileUpdatedWatchEvent;
use Spatie\TypeScriptTransformer\Events\Watch\WatchEvent;
use Spatie\TypeScriptTransformer\Handlers\Watch\ConfigUpdatedWatchEventHandler;
use Spatie\TypeScriptTransformer\Handlers\Watch\DirectoryDeletedWatchEventHandler;
use Spatie\TypeScriptTransformer\Handlers\Watch\FileDeletedWatchEventHandler;
use Spatie\TypeScriptTransformer\Handlers\Watch\FileUpdatedOrCreatedWatchEventHandler;
use Spatie\TypeScriptTransformer\Handlers\Watch\WatchEventHandler;
use Spatie\Watcher\Exceptions\CouldNotStartWatcher;
use Spatie\Watcher\Watch;

class FileSystemWatcher
{
    public const EXIT_CODE_COMPLETE_REFRESH = 42;

    protected array $eventsBuffer = [];

    protected bool $processing = false;

    /** @var array<class-string<WatchEvent>, array<WatchEventHandler>> */
    protected array $handlers = [];

    public function __construct(
        protected TypeScriptTransformer $typeScriptTransformer,
        protected TransformedCollection $transformedCollection,
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
            $this->typeScriptTransformer->log->info('Now watching for changes ...');

            $watcher->start();
        } catch (CouldNotStartWatcher $e) {
            throw new Exception(
                'Could not start watcher. Make sure you have required chokidar. (https://github.com/spatie/file-system-watcher?tab=readme-ov-file#installation)'
            );
        }
    }

    protected function initializeHandlers(): void
    {
        $this->handlers[FileCreatedWatchEvent::class] = [
            new FileUpdatedOrCreatedWatchEventHandler(
                $this->typeScriptTransformer,
                $this->transformedCollection,
            ),
        ];

        $this->handlers[FileUpdatedWatchEvent::class] = [
            new FileUpdatedOrCreatedWatchEventHandler(
                $this->typeScriptTransformer,
                $this->transformedCollection,
            ),
            new ConfigUpdatedWatchEventHandler(
                $this->typeScriptTransformer,
            ),
        ];

        $this->handlers[FileDeletedWatchEvent::class] = [
            new FileDeletedWatchEventHandler(
                $this->typeScriptTransformer,
                $this->transformedCollection,
            ),
        ];

        $this->handlers[DirectoryDeletedWatchEvent::class] = [
            new DirectoryDeletedWatchEventHandler(
                $this->typeScriptTransformer,
                $this->transformedCollection,
            ),
        ];
    }

    protected function processBuffer(): void
    {
        $this->typeScriptTransformer->log->info('Processing events');

        [$events, $this->eventsBuffer] = [$this->eventsBuffer, []];

        foreach ($events as $event) {
            foreach ($this->handlers[$event::class] as $handler) {
                $result = $handler->handle($event);

                if ($result instanceof WatchEventResult && $result->completeRefresh) {
                    ray('Triggering complete refresh');

                    exit(self::EXIT_CODE_COMPLETE_REFRESH);
                }
            }
        }

        $this->typeScriptTransformer->executeProvidedClosuresAction->execute(
            $this->transformedCollection->onlyChanged()
        );

        $this->typeScriptTransformer->connectReferencesAction->execute(
            $this->transformedCollection
        );

        $this->tryToConnectMissingReferencesWithNewTransformed();

        $this->typeScriptTransformer->executeConnectedClosuresAction->execute(
            $this->transformedCollection->onlyChanged()
        );

        $this->typeScriptTransformer->outputTransformed(
            $this->transformedCollection,
        );
    }

    protected function tryToConnectMissingReferencesWithNewTransformed(): void
    {
        foreach ($this->transformedCollection as $currentTransformed) {
            foreach ($currentTransformed->missingReferences as $missingReference => $typeReferences) {
                $foundTransformed = $this->transformedCollection->get($missingReference);

                if ($foundTransformed === null) {
                    continue;
                }

                $currentTransformed->markMissingReferenceFound($foundTransformed);

                break;
            }
        }
    }
}
