<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptGenericTypeVariable implements TypeScriptNode, TypeScriptNodeWithChildren
{
    public function __construct(
        public TypeScriptNode $identifier,
        public ?TypeScriptNode $extends = null,
        public ?TypeScriptNode $default = null,
    ) {
    }

    public function write(WritingContext $context): string
    {
        return "{$this->identifier->write($context)}".
            ($this->extends ? " extends {$this->extends?->write($context)}" : '').
            ($this->default ? " = {$this->default?->write($context)}" : '');
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
