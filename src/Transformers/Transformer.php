<?php

namespace Spatie\TypescriptTransformer\Transformers;

use ReflectionClass;
use Spatie\TypescriptTransformer\Structures\TransformedType;

interface Transformer
{
    public function canTransform(ReflectionClass $class): bool;

    public function transform(ReflectionClass $class, string $name): TransformedType;
}
