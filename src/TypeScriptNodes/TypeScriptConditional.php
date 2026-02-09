<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;
use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptConditional implements TypeScriptNode
{
    public function __construct(
        #[NodeVisitable]
        public TypeScriptNode $condition,
        #[NodeVisitable]
        public TypeScriptNode $ifTrue,
        #[NodeVisitable]
        public TypeScriptNode $ifFalse,
    ) {
    }

    public function write(WritingContext $context): string
    {
        return "{$this->condition->write($context)} ? {$this->ifTrue->write($context)} : {$this->ifFalse->write($context)}";
    }
}
