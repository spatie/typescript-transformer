<?php

namespace Spatie\TypeScriptTransformer\Exceptions;

use Exception;
use ReflectionClass;

class InvalidTransformerGiven extends Exception
{
    public static function classDoesNotExist(ReflectionClass $reflectionClass, string $transformerClass): self
    {
        return new self("The transformer ({$transformerClass}) defined in ({$reflectionClass->getName()}) does not exist!");
    }

    public static function classIsNotATransformer(ReflectionClass $reflectionClass, string $transformerClass)
    {
        return new self("The transformer ({$transformerClass}) defined in ({$reflectionClass->getName()}) does not implement the Transformer interface!");
    }
}
