<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptProperty implements TypeScriptNode, TypeScriptNodeWithChildren
{
    public TypeScriptIdentifier|TypeScriptIndexSignature $name;

    public function __construct(
        TypeScriptIdentifier|TypeScriptIndexSignature|string $name,
        public TypeScriptNode $type,
        public bool $isOptional = false,
        public bool $isReadonly = false,
    ) {
        $this->name = is_string($name) ? new TypeScriptIdentifier($name) : $name;
    }

    public function write(WritingContext $context): string
    {
        $readonly = $this->isReadonly ? 'readonly ' : '';
        $optional = $this->isOptional ? '?' : '';

        return "{$readonly}{$this->name->write($context)}{$optional}: {$this->type->write($context)}";
    }

    public function children(): array
    {
        return [$this->name, $this->type];
    }
}
