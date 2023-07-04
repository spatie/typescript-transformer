<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptGeneric implements TypeScriptNode, TypeScriptNodeWithChildren
{
    /**
     * @param array<TypeScriptNode> $genericTypes
     */
    public function __construct(
        public TypeScriptNode $type,
        public array $genericTypes,
    ) {
    }

    public function write(WritingContext $context): string
    {
        $generics = implode(', ', array_map(
            fn (TypeScriptNode $type) => $type->write($context),
            $this->genericTypes
        ));

        return "{$this->type->write($context)}<{$generics}>";
    }

    public function children(): array
    {
        return [
            $this->type,
            ...$this->genericTypes,
        ];
    }
}
