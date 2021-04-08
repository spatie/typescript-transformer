<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes;

use ReflectionProperty;

class FakeReflectionProperty extends ReflectionProperty
{
    use FakedReflection;
}
