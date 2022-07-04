<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes;

use ReflectionClass;
use ReflectionProperty;

class FakeReflectionProperty extends ReflectionProperty
{
    use FakedReflection;

    public function getDeclaringClass(): ReflectionClass
    {
        return new ReflectionClass(new class {
        });
    }
}
