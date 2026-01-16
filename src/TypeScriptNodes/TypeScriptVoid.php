<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptVoid implements TypeScriptNode
{
    public function write(WritingContext $context): string
    {
        return 'void';
    }
}
