<?php

namespace Spatie\TypeScriptTransformer\EventHandlers;

use Spatie\TypeScriptTransformer\Data\WatchEventResult;
use Spatie\TypeScriptTransformer\Events\WatchEvent;

/**
 * @template T of WatchEvent
 */
interface WatchEventHandler
{
    /**
     * @param T $event
     */
    public function handle($event): ?WatchEventResult;
}
