<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\Concerns\OutputsTypeScriptLiteral;

class TypeScriptLiteral implements TypeScriptNode
{
    use OutputsTypeScriptLiteral;

    public function __construct(public int|string|float|bool|null $value)
    {
    }

    public function write(WritingContext $context): string
    {
        return $this->outputLiteral($this->value);
    }
}
