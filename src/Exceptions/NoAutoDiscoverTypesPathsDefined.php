<?php

namespace Spatie\TypeScriptTransformer\Exceptions;

use Exception;

class NoAutoDiscoverTypesPathsDefined extends Exception
{
    public static function create(): self
    {
        return new self("There were no auto discover types paths defined");
    }
}
