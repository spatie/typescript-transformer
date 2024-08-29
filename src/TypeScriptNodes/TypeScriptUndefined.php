<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptUndefined implements TypeScriptNode
{
    public function write(WritingContext $context): string
    {
        return 'undefined';
    }
}
