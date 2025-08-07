<?php

namespace Spatie\TypeScriptTransformer\Support\Concerns;

trait Instanceable
{
    protected static ?self $instance = null;

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }
}
