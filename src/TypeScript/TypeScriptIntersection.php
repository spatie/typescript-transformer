<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptIntersection implements TypeScriptNode, TypeScriptNodeWithChildren
{
    /**
     * @param  array<TypeScriptNode>  $types
     */
    public function __construct(
        public array $types,
    ) {
    }

    public function write(WritingContext $context): string
    {
        return implode(' & ', array_map(
            fn (TypeScriptNode $type) => $type->write($context),
            $this->types
        ));
    }

    public function children(): array
    {
        return $this->types;
    }
}
