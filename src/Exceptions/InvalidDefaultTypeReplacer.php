<?php

namespace Spatie\TypeScriptTransformer\Exceptions;

use Exception;

class InvalidDefaultTypeReplacer extends Exception
{
    public static function classDoesNotExist(string $class): self
    {
        return new self("Type processor could not replace class: `{{ $class }}` because it does not exist");
    }
}
