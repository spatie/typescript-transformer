<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptFunction implements TypeScriptNode
{
    public function write(WritingContext $context): string
    {
        return 'function'; // to extend
    }
}
