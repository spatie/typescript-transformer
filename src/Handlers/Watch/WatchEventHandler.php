<?php

namespace Spatie\TypeScriptTransformer\Handlers\Watch;

use Spatie\TypeScriptTransformer\Data\WatchEventResult;
use Spatie\TypeScriptTransformer\Events\Watch\WatchEvent;

/**
 * @template T of WatchEvent
 */
interface WatchEventHandler
{
    /**
     * @param T $event
     */
    public function handle($event): WatchEventResult|int;
}
