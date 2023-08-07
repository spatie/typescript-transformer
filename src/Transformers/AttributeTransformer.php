<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use ReflectionClass;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Support\TransformationContext;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformed\Untransformable;

class AttributeTransformer extends ClassTransformer
{
    protected function shouldTransform(ReflectionClass $reflection): bool
    {
        return count($reflection->getAttributes(TypeScript::class)) > 0;
    }

    public function transform(ReflectionClass $reflectionClass, TransformationContext $context): Transformed|Untransformable
    {
        $transformed = parent::transform($reflectionClass, $context);

        if ($transformed instanceof Untransformable) {
            return $transformed;
        }

        /** @var TypeScript $attribute */
        $attribute = $reflectionClass->getAttributes(TypeScript::class)[0]->newInstance();

        if ($attribute->name !== null) {
            $transformed->nameAs($attribute->name);
        }

        if ($attribute->location !== null) {
            $transformed->location = $attribute->location;
        }

        return $transformed;
    }
}
