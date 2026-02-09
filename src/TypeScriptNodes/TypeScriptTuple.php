<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;
use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptTuple implements TypeScriptNode
{
    /**
     * @param  TypeScriptNode[]  $types
     */
    public function __construct(
        #[NodeVisitable]
        public array $types
    ) {
    }

    public function write(WritingContext $context): string
    {
        $types = implode(', ', array_map(
            fn (TypeScriptNode $type) => $type->write($context),
            $this->types
        ));

        return "[$types]";
    }
}
