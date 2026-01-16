<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptNever implements TypeScriptNode
{
    public function write(WritingContext $context): string
    {
        return 'never';
    }
}
