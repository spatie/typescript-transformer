<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptAny implements TypeScriptNode
{
    public function write(WritingContext $context): string
    {
        return 'any';
    }
}
