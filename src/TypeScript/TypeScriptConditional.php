<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptConditional implements TypeScriptNode, TypeScriptNodeWithChildren
{
    public function __construct(
        public TypeScriptNode $condition,
        public TypeScriptNode $ifTrue,
        public TypeScriptNode $ifFalse,
    ) {
    }

    public function write(WritingContext $context): string
    {
        return "{$this->condition->write($context)} ? {$this->ifTrue->write($context)} : {$this->ifFalse->write($context)}";
    }

    public function children(): array
    {
        return [$this->condition, $this->ifTrue, $this->ifFalse];
    }
}
