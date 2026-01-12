<?php

namespace Spatie\TypeScriptTransformer\Events\Watch;

class FileDeletedWatchEvent extends WatchEvent
{
    public function __construct(
        public string $path
    ) {
    }
}
