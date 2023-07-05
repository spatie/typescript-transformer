<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptNull implements TypeScriptNode
{
    public function write(WritingContext $context): string
    {
        return 'null';
    }
}
