<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptParameter implements TypeScriptNode, TypeScriptNodeWithChildren
{
    public function __construct(
        public string $name,
        public TypeScriptNode $type,
        public bool $isOptional = false,
    ) {
    }

    public function write(WritingContext $context): string
    {
        $name = ! preg_match('/^[$_a-zA-Z][$_a-zA-Z0-9]*$/', $this->name)
            ? "'{$this->name}'"
            : $this->name;

        return $this->isOptional
            ? "{$name}?: {$this->type->write($context)}"
            : "{$name}: {$this->type->write($context)}";
    }

    public function children(): array
    {
        return [$this->type];
    }
}
