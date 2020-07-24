<?php

namespace Spatie\TypescriptTransformer\Tests\Fakes;

use ReflectionProperty;

class FakePropertyReflection extends ReflectionProperty
{
    public static function create(): self
    {
        return new self();
    }

    public function __construct()
    {

    }
}
