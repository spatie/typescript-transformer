<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Support\WritingContext;

interface TypeScriptNode
{
    public function write(WritingContext $context): string;
}
