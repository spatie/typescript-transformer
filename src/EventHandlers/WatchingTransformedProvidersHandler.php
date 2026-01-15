<?php

namespace Spatie\TypeScriptTransformer\EventHandlers;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Data\WatchEventResult;
use Spatie\TypeScriptTransformer\TransformedProviders\WatchingTransformedProvider;

class WatchingTransformedProvidersHandler implements WatchEventHandler
{
    public function __construct(
        protected WatchingTransformedProvider $transformedProvider,
        protected TransformedCollection $transformedCollection,
    ) {
    }

    public function handle($event): ?WatchEventResult
    {
        return $this->transformedProvider->handleWatchEvent($event, $this->transformedCollection);
    }
}
