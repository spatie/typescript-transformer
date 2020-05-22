<?php

namespace Spatie\TypescriptTransformer\Transformers;

use ReflectionClass;

interface Transformer
{
    public function canTransform(ReflectionClass $class): bool;

    public function transform(ReflectionClass $class, string $name): string;
}
