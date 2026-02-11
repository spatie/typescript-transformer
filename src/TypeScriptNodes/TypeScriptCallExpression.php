<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;
use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptCallExpression implements TypeScriptNode
{
    /**
     * @param  array<TypeScriptNode>  $arguments
     * @param  array<TypeScriptNode>  $genericTypes
     */
    public function __construct(
        #[NodeVisitable]
        public TypeScriptNode $callee,
        #[NodeVisitable]
        public array $arguments = [],
        #[NodeVisitable]
        public array $genericTypes = [],
    ) {
    }

    public function write(WritingContext $context): string
    {
        $generics = '';

        if (! empty($this->genericTypes)) {
            $generics = '<'.implode(', ', array_map(
                fn (TypeScriptNode $type) => $type->write($context),
                $this->genericTypes,
            )).'>';
        }

        $args = implode(', ', array_map(
            fn (TypeScriptNode $arg) => $arg->write($context),
            $this->arguments,
        ));

        return "{$this->callee->write($context)}{$generics}({$args})";
    }
}
