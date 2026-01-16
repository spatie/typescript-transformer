<?php

namespace Spatie\TypeScriptTransformer\Data;

readonly class WatchEventResult
{
    public function __construct(
        public bool $completeRefresh,
    ) {
    }

    public static function continue(): self
    {
        return new self(false);
    }

    public static function completeRefresh(): self
    {
        return new self(true);
    }
}
