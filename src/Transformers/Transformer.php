<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use ReflectionClass;
use Spatie\TypeScriptTransformer\Structures\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Structures\OldTransformedType;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

interface Transformer
{
    public function canTransform(ReflectionClass $class): bool;

    public function transform(ReflectionClass $class, ?string $name = null): Transformed;
}
