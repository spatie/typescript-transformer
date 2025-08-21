<?php

namespace Spatie\TypeScriptTransformer\TypeProviders;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Data\WatchEventResult;
use Spatie\TypeScriptTransformer\Events\Watch\WatchEvent;

interface WatchingTypesProvider
{
    /** @return array<string> */
    public function directoriesToWatch(): array;

    public function handleWatchEvent(WatchEvent $watchEvent, TransformedCollection $transformedCollection): int|WatchEventResult;
}
