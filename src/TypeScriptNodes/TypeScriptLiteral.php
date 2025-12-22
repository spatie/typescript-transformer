<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptLiteral implements TypeScriptNode
{
    public function __construct(public int|string|bool $value)
    {
    }

    public function write(WritingContext $context): string
    {
        return json_encode($this->value);
    }
}
