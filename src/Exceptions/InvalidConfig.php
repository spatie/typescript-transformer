<?php

namespace Spatie\TypescriptTransformer\Exceptions;

use Exception;

class InvalidConfig extends Exception
{
    public static function missingSearchingPath(): self
    {
        return new self('The searching path in the config is missing');
    }

    public static function missingOutputFile(): self
    {
        return new self('The output file in the config is missing');
    }
}
