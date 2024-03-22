<?php

namespace Spatie\TypeScriptTransformer\Support\Concerns;

trait Instanceable
{
    protected static ?self $instance = null;

    public static function instance(): static
    {
        return static::$instance ??= new static();
    }
}
