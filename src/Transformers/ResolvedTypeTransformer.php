<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use ReflectionClass;
use Spatie\TypeScriptTransformer\Structures\TransformedType;

class ResolvedTypeTransformer implements Transformer
{
    public function canTransform(ReflectionClass $class): bool
    {
        return true;
    }

    public function transform(ReflectionClass $class, string $name): TransformedType
    {

    }
}
