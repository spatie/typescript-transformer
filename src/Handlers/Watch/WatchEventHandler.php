<?php

namespace Spatie\TypeScriptTransformer\Handlers\Watch;

use Spatie\TypeScriptTransformer\Events\Watch\WatchEvent;

/**
 * @template T of WatchEvent
 */
interface WatchEventHandler
{
    /**
     * @param T $event
     */
    public function handle($event): void;
}
