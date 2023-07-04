<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptString implements TypeScriptNode
{
    public function write(WritingContext $context): string
    {
        return 'string';
    }
}
