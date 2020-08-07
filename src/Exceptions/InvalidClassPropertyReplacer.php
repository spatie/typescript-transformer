<?php

namespace Spatie\TypescriptTransformer\Exceptions;

use Exception;

class InvalidClassPropertyReplacer extends Exception
{
    public static function classDoesNotExist(string $class): self
    {
        return new self("Property replacer could not replace class: `{{ $class }}` because it does not exist");
    }
}
