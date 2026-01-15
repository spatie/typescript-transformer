<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptUndefined implements TypeScriptNode
{
    public function write(WritingContext $context): string
    {
        return 'undefined';
    }
}
