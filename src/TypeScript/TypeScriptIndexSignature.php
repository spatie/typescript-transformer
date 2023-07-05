<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptIndexSignature implements TypeScriptNode, TypeScriptNodeWithChildren
{
    public function __construct(
        public TypeScriptNode $type,
        public string $name = 'index',
    ) {
    }

    public function write(WritingContext $context): string
    {
        return "[{$this->name}: {$this->type->write($context)}]]";
    }

    public function children(): array
    {
        return [$this->type];
    }
}
