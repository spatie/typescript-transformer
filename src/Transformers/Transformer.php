<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\Support\TransformationContext;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformed\Untransformable;

interface Transformer
{
    public function transform(PhpClassNode $phpClassNode, TransformationContext $context): Transformed|Untransformable;
}
