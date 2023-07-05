<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptUnknown implements TypeScriptNode
{
    public function write(WritingContext $context): string
    {
        return 'unknown';
    }
}
