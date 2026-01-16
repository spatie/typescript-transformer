<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptUnknown implements TypeScriptNode
{
    public function write(WritingContext $context): string
    {
        return 'unknown';
    }
}
