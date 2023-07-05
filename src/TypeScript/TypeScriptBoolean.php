<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptBoolean implements TypeScriptNode
{
    public function write(WritingContext $context): string
    {
        return 'boolean';
    }
}
