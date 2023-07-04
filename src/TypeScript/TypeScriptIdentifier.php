<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptIdentifier implements TypeScriptNode
{
    public function __construct(
        public string $name,
    )
    {
    }

    public function write(WritingContext $context): string
    {
        return $this->name;
    }
}
