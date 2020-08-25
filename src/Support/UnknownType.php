<?php

namespace Spatie\TypescriptTransformer\Support;

use phpDocumentor\Reflection\Type;

/** @psalm-immutable */
class UnknownType implements Type
{
    public function __toString(): string
    {
        return 'never';
    }
}
