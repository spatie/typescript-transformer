<?php

namespace Spatie\TypeScriptTransformer\Exceptions;

use Exception;

class CircularDependencyChain extends Exception
{
    public static function create(array $chain): self
    {
        return new self("Circular dependency chain found: " . implode(' -> ', $chain));
    }
}
