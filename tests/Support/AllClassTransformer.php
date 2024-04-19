<?php

namespace Spatie\TypeScriptTransformer\Tests\Support;

use ReflectionClass;
use Spatie\TypeScriptTransformer\Transformers\ClassTransformer;

class AllClassTransformer extends ClassTransformer
{
    protected function shouldTransform(ReflectionClass $reflection): bool
    {
        return true;
    }
}
