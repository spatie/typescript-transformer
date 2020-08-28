<?php

namespace Spatie\TypeScriptTransformer\Exceptions;

use Exception;
use ReflectionClass;

class TransformerNotFound extends Exception
{
    public static function create(ReflectionClass $class): self
    {
        return new self("Could not find transformer for: {$class->getName()}!");
    }
}
