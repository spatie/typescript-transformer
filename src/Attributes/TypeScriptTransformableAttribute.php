<?php

namespace Spatie\TypeScriptTransformer\Attributes;

use phpDocumentor\Reflection\Type;

interface TypeScriptTransformableAttribute
{
    public function getType(): Type;
}
