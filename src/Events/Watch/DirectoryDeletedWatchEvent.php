<?php

namespace Spatie\TypeScriptTransformer\Events\Watch;

class DirectoryDeletedWatchEvent extends WatchEvent
{
    public function __construct(
        public string $path
    ) {
    }
}
