<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptRaw implements TypeScriptNode
{
    public function __construct(
        public string $typeScript
    ) {
    }

    public function write(WritingContext $context): string
    {
        return $this->typeScript;
    }
}
