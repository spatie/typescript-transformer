<?php

namespace Spatie\TypeScriptTransformer\Events;

class DirectoryDeletedWatchEvent extends WatchEvent
{
    public function __construct(
        public string $path
    ) {
    }
}
