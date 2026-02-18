<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;
use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptGeneric implements TypeScriptForwardingNamedNode, TypeScriptNode
{
    /**
     * @param  array<TypeScriptNode>  $genericTypes
     */
    public function __construct(
        #[NodeVisitable]
        public TypeScriptIdentifier|TypeScriptReference $type,
        #[NodeVisitable]
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

    public function getForwardedNamedNode(): TypeScriptNamedNode|TypeScriptForwardingNamedNode
    {
        return $this->type;
    }
}
