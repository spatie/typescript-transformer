<?php

namespace Spatie\TypeScriptTransformer\Handlers\Watch;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Data\WatchEventResult;
use Spatie\TypeScriptTransformer\TypeProviders\WatchingTypesProvider;

class WatchingTypesProvidersHandler implements WatchEventHandler
{
    public function __construct(
        protected WatchingTypesProvider $typeProvider,
        protected TransformedCollection $transformedCollection,
    ) {
    }

    public function handle($event): WatchEventResult|int
    {
        return $this->typeProvider->handleWatchEvent($event, $this->transformedCollection);
    }
}
