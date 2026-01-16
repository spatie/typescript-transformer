<?php

namespace Spatie\TypeScriptTransformer\Attributes;

use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;

interface TypeScriptTypeAttributeContract
{
    public function getType(PhpClassNode $class): TypeScriptNode;
}
