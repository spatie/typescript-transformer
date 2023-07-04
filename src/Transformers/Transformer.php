<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use ReflectionClass;
use Spatie\TypeScriptTransformer\Support\TransformationContext;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformed\Untransformable;

interface Transformer
{
    public function transform(ReflectionClass $reflectionClass, TransformationContext $context): Transformed|Untransformable;
}
