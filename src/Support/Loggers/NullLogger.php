<?php

namespace Spatie\TypeScriptTransformer\Support\Loggers;

class NullLogger implements Logger
{
    public function error(mixed $item, ?string $title = null): void
    {

    }

    public function info(mixed $item, ?string $title = null): void
    {

    }

    public function warn(mixed $item, ?string $title = null): void
    {

    }

    public function debug(mixed $item, ?string $title = null): void
    {

    }
}
