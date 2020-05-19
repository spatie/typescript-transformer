<?php

namespace Spatie\TypescriptTransformer\Exceptions;

use Exception;
use ReflectionClass;

class MapperNotFound extends Exception
{
    public static function create(ReflectionClass $class): self
    {
        return new self("Could not find mapper for: {$class->getName()}");
    }
}
