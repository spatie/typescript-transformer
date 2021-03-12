<?php

namespace Spatie\TypeScriptTransformer\Exceptions;

use Exception;

class NoSearchingPathsDefined extends Exception
{
    public static function create(): self
    {
        return new self("There were no searching paths defined");
    }
}
