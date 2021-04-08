<?php

namespace Spatie\TypeScriptTransformer\Exceptions;

use Exception;

class UnableToTransformUsingAttribute extends Exception
{
    public static function create(mixed $context): self
    {
        $jsonContext = json_encode($context);

        throw new self("Could not transform to typescript with attribute, context:: `{$jsonContext}`");
    }
}
