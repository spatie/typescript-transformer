<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptFunction implements TypeScriptNode
{
    public function write(WritingContext $context): string
    {
        return 'function'; // to extend
    }
}
