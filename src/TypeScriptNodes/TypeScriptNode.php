<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Data\WritingContext;

interface TypeScriptNode
{
    public function write(WritingContext $context): string;
}
