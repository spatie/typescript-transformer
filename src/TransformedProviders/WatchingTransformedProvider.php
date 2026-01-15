<?php

namespace Spatie\TypeScriptTransformer\TransformedProviders;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Data\WatchEventResult;
use Spatie\TypeScriptTransformer\Events\WatchEvent;

interface WatchingTransformedProvider
{
    /** @return array<string> */
    public function directoriesToWatch(): array;

    public function handleWatchEvent(WatchEvent $watchEvent, TransformedCollection $transformedCollection): int|WatchEventResult;
}
