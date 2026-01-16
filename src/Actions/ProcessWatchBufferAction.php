<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Collections\WritersCollection;
use Spatie\TypeScriptTransformer\Data\WatchEventResult;
use Spatie\TypeScriptTransformer\EventHandlers\ConfigUpdatedWatchEventHandler;
use Spatie\TypeScriptTransformer\EventHandlers\DirectoryDeletedWatchEventHandler;
use Spatie\TypeScriptTransformer\EventHandlers\FileDeletedWatchEventHandler;
use Spatie\TypeScriptTransformer\EventHandlers\FileUpdatedOrCreatedWatchEventHandler;
use Spatie\TypeScriptTransformer\EventHandlers\WatchEventHandler;
use Spatie\TypeScriptTransformer\EventHandlers\WatchingTransformedProvidersHandler;
use Spatie\TypeScriptTransformer\Events\DirectoryDeletedWatchEvent;
use Spatie\TypeScriptTransformer\Events\FileCreatedWatchEvent;
use Spatie\TypeScriptTransformer\Events\FileDeletedWatchEvent;
use Spatie\TypeScriptTransformer\Events\FileUpdatedWatchEvent;
use Spatie\TypeScriptTransformer\Events\SummarizedWatchEvent;
use Spatie\TypeScriptTransformer\Events\WatchEvent;
use Spatie\TypeScriptTransformer\TransformedProviders\WatchingTransformedProvider;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;

class LProcessWatchBufferAction
{
    /** @var array<class-string<WatchEvent>, array<WatchEventHandler>> */
    protected array $handlers = [];

    public function __construct(
        protected TypeScriptTransformer $typeScriptTransformer,
        protected TransformedCollection $transformedCollection,
        protected WritersCollection $writersCollection,
    ) {
        $this->initializeHandlers();
    }

    /**
     * @param array<WatchEvent> $events
     */
    public function execute(array $events): ?WatchEventResult
    {
        $this->typeScriptTransformer->logger->info('Processing events');

        if (count($events) === 0) {
            return null;
        }

        $summarizedEvent = new SummarizedWatchEvent();

        foreach ($events as $event) {
            $summarizedEvent->handleWatchEvent($event);

            foreach ($this->handlers[$event::class] as $handler) {
                $result = $handler->handle($event);

                if ($result?->completeRefresh) {
                    return $result;
                }
            }
        }

        foreach ($this->handlers[SummarizedWatchEvent::class] as $handler) {
            $handler->handle($summarizedEvent);
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
            $this->writersCollection,
        );

        return null;
    }

    protected function initializeHandlers(): void
    {
        $fileUpdatedOrCreatedHandler = new FileUpdatedOrCreatedWatchEventHandler(
            $this->typeScriptTransformer,
            $this->transformedCollection,
        );

        $watchingTransformedProviders = array_values(array_map(
            fn (WatchingTransformedProvider $provider) => new WatchingTransformedProvidersHandler(
                $provider,
                $this->transformedCollection
            ),
            array_filter(
                $this->typeScriptTransformer->config->transformedProviders,
                fn ($provider) => $provider instanceof WatchingTransformedProvider
            )
        ));

        $this->handlers[FileCreatedWatchEvent::class] = [
            $fileUpdatedOrCreatedHandler,
            ...$watchingTransformedProviders,
        ];

        $this->handlers[FileUpdatedWatchEvent::class] = [
            $fileUpdatedOrCreatedHandler,
            new ConfigUpdatedWatchEventHandler($this->typeScriptTransformer),
            ...$watchingTransformedProviders,
        ];

        $this->handlers[FileDeletedWatchEvent::class] = [
            new FileDeletedWatchEventHandler(
                $this->typeScriptTransformer,
                $this->transformedCollection,
            ),
            ...$watchingTransformedProviders,
        ];

        $this->handlers[DirectoryDeletedWatchEvent::class] = [
            new DirectoryDeletedWatchEventHandler(
                $this->typeScriptTransformer,
                $this->transformedCollection,
            ),
            ...$watchingTransformedProviders,
        ];

        $this->handlers[SummarizedWatchEvent::class] = [
            ...$watchingTransformedProviders,
        ];
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
