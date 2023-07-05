<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\WritingContext;

interface TypeScriptNode
{
    /** @todo rename this namespace */
    public function write(WritingContext $context): string;
}
