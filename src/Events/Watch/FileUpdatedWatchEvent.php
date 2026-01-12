<?php

namespace Spatie\TypeScriptTransformer\Events\Watch;

class FileUpdatedWatchEvent extends WatchEvent
{
    public function __construct(
        public string $path
    ) {
    }
}
