<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;
use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\Concerns\UniqueTypeScriptNodes;

class TypeScriptArray implements TypeScriptNode
{
    use UniqueTypeScriptNodes;

    /**
     * @param TypeScriptNode[] $types
     */
    public function __construct(
        #[NodeVisitable]
        public array $types
    ) {
        $this->types = $this->uniqueNodes($this->types);
    }

    public function write(WritingContext $context): string
    {
        $types = implode('| ', array_map(
            fn (TypeScriptNode $type) => $type->write($context),
            $this->types
        ));

        if (count($this->types) > 1) {
            $types = "($types)";
        }

        return "{$types}[]";
    }
}
