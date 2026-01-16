<?php

namespace Spatie\TypeScriptTransformer;

use Exception;
use Spatie\TypeScriptTransformer\Actions\ProcessWatchBufferAction;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Events\DirectoryDeletedWatchEvent;
use Spatie\TypeScriptTransformer\Events\FileCreatedWatchEvent;
use Spatie\TypeScriptTransformer\Events\FileDeletedWatchEvent;
use Spatie\TypeScriptTransformer\Events\FileUpdatedWatchEvent;
use Spatie\Watcher\Exceptions\CouldNotStartWatcher;
use Spatie\Watcher\Watch;

class FileSystemWatcher
{
    public const EXIT_CODE_COMPLETE_REFRESH = 42;

    protected array $eventsBuffer = [];

    protected bool $processing = false;

    protected ProcessWatchBufferAction $processWatchBufferAction;

    public function __construct(
        protected TypeScriptTransformer $typeScriptTransformer,
        protected TransformedCollection $transformedCollection,
    ) {
        $this->processWatchBufferAction = new ProcessWatchBufferAction(
            $this->typeScriptTransformer,
            $this->transformedCollection,
        );
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
            $this->typeScriptTransformer->logger->info('Now watching for changes ...');

            $watcher->start();
        } catch (CouldNotStartWatcher $e) {
            throw new Exception(
                'Could not start watcher. Make sure you have required chokidar. (https://github.com/spatie/file-system-watcher?tab=readme-ov-file#installation)'
            );
        }
    }

    protected function processBuffer(): void
    {
        [$events, $this->eventsBuffer] = [$this->eventsBuffer, []];

        $result = $this->processWatchBufferAction->execute($events);

        if ($result?->completeRefresh) {
            $this->typeScriptTransformer->logger->info('Triggering complete refresh');

            exit(self::EXIT_CODE_COMPLETE_REFRESH);
        }
    }
}
