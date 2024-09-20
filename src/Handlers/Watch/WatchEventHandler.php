<?php

namespace Spatie\TypeScriptTransformer\Handlers\Watch;

interface WatchEventHandler
{
    public function handle($event): void;
}
