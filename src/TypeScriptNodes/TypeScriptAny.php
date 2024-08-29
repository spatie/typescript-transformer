<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptAny implements TypeScriptNode
{
    public function write(WritingContext $context): string
    {
        return 'any';
    }
}
