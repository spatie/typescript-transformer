<?php

namespace Spatie\TypeScriptTransformer\Handlers\Watch;

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

    public function handle($event): WatchEventResult|int
    {
        return $this->transformedProvider->handleWatchEvent($event, $this->transformedCollection);
    }
}
