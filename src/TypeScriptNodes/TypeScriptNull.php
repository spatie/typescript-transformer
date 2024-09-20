<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptNull implements TypeScriptNode
{
    public function write(WritingContext $context): string
    {
        return 'null';
    }
}