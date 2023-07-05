<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptVoid implements TypeScriptNode
{
    public function write(WritingContext $context): string
    {
        return 'void';
    }
}
