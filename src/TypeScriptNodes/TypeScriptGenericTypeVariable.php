<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;
use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptGenericTypeVariable implements TypeScriptNode
{
    public function __construct(
        #[NodeVisitable]
        public TypeScriptNode $identifier,
        #[NodeVisitable]
        public ?TypeScriptNode $extends = null,
        #[NodeVisitable]
        public ?TypeScriptNode $default = null,
    ) {
    }

    public function write(WritingContext $context): string
    {
        return "{$this->identifier->write($context)}".
            ($this->extends ? " extends {$this->extends->write($context)}" : '').
            ($this->default ? " = {$this->default->write($context)}" : '');
    }

    public function children(): array
    {
        return array_filter([
            $this->identifier,
            $this->extends,
            $this->default,
        ]);
    }
}
