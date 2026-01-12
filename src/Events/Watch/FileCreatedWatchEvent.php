<?php

namespace Spatie\TypeScriptTransformer\Events\Watch;

class FileCreatedWatchEvent extends WatchEvent
{
    public function __construct(
        public string $path
    ) {
    }
}
