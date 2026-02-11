<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptObjectLiteral implements TypeScriptNode
{
    public function __construct(
        public array $data,
    ) {
    }

    public function write(WritingContext $context): string
    {
        return json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
