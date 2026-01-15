<?php

namespace Spatie\TypeScriptTransformer\Events;

class FileCreatedWatchEvent extends WatchEvent
{
    public function __construct(
        public string $path
    ) {
    }
}
