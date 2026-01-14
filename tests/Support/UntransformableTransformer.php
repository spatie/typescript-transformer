<?php

namespace Spatie\TypeScriptTransformer\Tests\Support;

use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\Support\TransformationContext;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformed\Untransformable;
use Spatie\TypeScriptTransformer\Transformers\Transformer;

class UntransformableTransformer implements Transformer
{
    public function transform(PhpClassNode $phpClassNode, TransformationContext $context): Transformed|Untransformable
    {
        return new Untransformable();
    }
}
