<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use ReflectionClass;
use Spatie\TypeScriptTransformer\Structures\TransformedType;

interface Transformer
{
    public function canTransform(ReflectionClass $class): bool;

    public function transform(ReflectionClass $class, string $name): TransformedType;
}
