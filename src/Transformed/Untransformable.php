<?php

namespace Spatie\TypeScriptTransformer\Transformed;

use Spatie\TypeScriptTransformer\Support\Concerns\Instanceable;

class Untransformable
{
    use Instanceable;

    public static function create(): self
    {
        return self::instance();
    }

    private function __construct()
    {
    }
}
