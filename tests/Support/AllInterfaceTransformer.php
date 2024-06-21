<?php

namespace Spatie\TypeScriptTransformer\Tests\Support;

use ReflectionClass;
use Spatie\TypeScriptTransformer\Transformers\InterfaceTransformer;

class AllInterfaceTransformer extends InterfaceTransformer
{
    protected function shouldTransform(ReflectionClass $reflection): bool
    {
        return true;
    }
}
