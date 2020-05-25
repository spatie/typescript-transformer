<?php

namespace Spatie\TypescriptTransformer\Exceptions;

use Exception;

class InvalidConfig extends Exception
{
    public static function missingSearchingPath(): self
    {
        return new self('The searching path in the config is missing');
    }

    public static function missingDefaultFile(): self
    {
        return new self('The default file in the config is missing');
    }

    public static function missingTransformers()
    {
        return new self('No transformers were defined in the config');
    }

    public static function missingOutputPath(): self
    {
        return new self('The output path in the config is missing');
    }
}
