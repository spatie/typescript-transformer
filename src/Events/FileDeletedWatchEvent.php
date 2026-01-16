<?php

namespace Spatie\TypeScriptTransformer\Events;

class FileDeletedWatchEvent extends WatchEvent
{
    public function __construct(
        public string $path
    ) {
    }
}
