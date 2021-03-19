<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes;

use ReflectionProperty;
use ReflectionType;

class FakeReflectionProperty extends ReflectionProperty
{
    use FakedReflection;
}
