<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptBoolean implements TypeScriptNode
{
    public function write(WritingContext $context): string
    {
        return 'boolean';
    }
}
