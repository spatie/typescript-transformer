<?php

namespace Spatie\TypeScriptTransformer\Attributes;

use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;

interface TypeScriptTransformableAttribute
{
    public function getType(): TypeScriptNode;
}
