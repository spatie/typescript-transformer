<?php

namespace Spatie\TypeScriptTransformer\Events\Watch;

abstract class WatchEvent
{
    public function __construct(
        public string $path
    ) {
    }
}
