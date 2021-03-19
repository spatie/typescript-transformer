<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes;

use ReflectionMethod;

class FakeReflectionMethod extends ReflectionMethod
{
    use FakedReflection;
}
