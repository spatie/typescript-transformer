<?php

namespace Spatie\TypescriptTransformer\Exceptions;

use Exception;
use ReflectionClass;

class InvalidTransformerGiven extends Exception
{
    public static function classDoesNotExist(ReflectionClass $reflectionClass, string $class): self
    {
        return new self("The transformer ({$class}) defined in ({$reflectionClass->getName()}) does not exist!");
    }

    public static function classIsNotATransformer(ReflectionClass $reflectionClass, string $class)
    {
        return new self("The transformer ({$class}) defined in ({$reflectionClass->getName()}) does not implement the Transformer interface!");
    }
}
