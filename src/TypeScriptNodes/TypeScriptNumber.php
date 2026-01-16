<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptNumber implements TypeScriptNode
{
    public function write(WritingContext $context): string
    {
        return 'number';
    }
}
