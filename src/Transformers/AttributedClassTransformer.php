<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Data\TransformationContext;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformed\Untransformable;

class AttributedClassTransformer extends ClassTransformer
{
    protected function shouldTransform(PhpClassNode $phpClassNode): bool
    {
        return count($phpClassNode->getAttributes(TypeScript::class)) > 0;
    }

    public function transform(PhpClassNode $phpClassNode, TransformationContext $context): Transformed|Untransformable
    {
        $transformed = parent::transform($phpClassNode, $context);

        if ($transformed instanceof Untransformable) {
            return $transformed;
        }

        /** @var TypeScript $attribute */
        $attribute = $phpClassNode->getAttributes(TypeScript::class)[0]->getRawArguments();

        if (($attribute['name'] ?? null) !== null) {
            $transformed->nameAs($attribute['name']);
        }

        if (($attribute['location'] ?? null) !== null) {
            $transformed->location = $attribute['location'];
        }

        return $transformed;
    }
}
