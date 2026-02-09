<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;
use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptIndexSignature implements TypeScriptNode
{
    public function __construct(
        #[NodeVisitable]
        public TypeScriptNode $type,
        public string $name = 'index',
    ) {
    }

    public function write(WritingContext $context): string
    {
        return "[{$this->name}: {$this->type->write($context)}]";
    }
}
