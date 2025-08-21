<?php

namespace Spatie\TypeScriptTransformer\Handlers\Watch;

use Spatie\TypeScriptTransformer\Data\WatchEventResult;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;

class ConfigUpdatedWatchEventHandler implements WatchEventHandler
{
    public function __construct(
        private TypeScriptTransformer $typeScriptTransformer,
    ) {
    }

    public function handle($event): WatchEventResult|int
    {
        if (! in_array($event->path, $this->typeScriptTransformer->config->configPaths)) {
            return 0;
        }

        $this->typeScriptTransformer->log->info("Configuration file updated: {$event->path}, restarting worker...");

        return WatchEventResult::completeRefresh();
    }
}
