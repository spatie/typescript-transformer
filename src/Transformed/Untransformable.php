<?php

namespace Spatie\TypeScriptTransformer\Transformed;

class Untransformable
{
    protected static ?self $self = null;

    public static function create(): self
    {
        return static::$self ??= new static();
    }

    private function __construct()
    {
    }
}
