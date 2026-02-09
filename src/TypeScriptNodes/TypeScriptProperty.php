<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;
use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptProperty implements TypeScriptNode
{
    #[NodeVisitable]
    public TypeScriptIdentifier|TypeScriptIndexSignature $name;

    public function __construct(
        TypeScriptIdentifier|TypeScriptIndexSignature|string $name,
        #[NodeVisitable]
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
