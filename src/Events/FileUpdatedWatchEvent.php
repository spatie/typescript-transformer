<?php

namespace Spatie\TypeScriptTransformer\Events;

class FileUpdatedWatchEvent extends WatchEvent
{
    public function __construct(
        public string $path
    ) {
    }
}
