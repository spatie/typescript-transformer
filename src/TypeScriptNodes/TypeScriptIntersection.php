<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;
use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\Concerns\UniqueTypeScriptNodes;

class TypeScriptIntersection implements TypeScriptNode, TypeScriptDeduplicableNode
{
    use UniqueTypeScriptNodes;

    /**
     * @param  array<TypeScriptNode>  $types
     */
    public function __construct(
        #[NodeVisitable]
        public array $types,
    ) {
        $this->deduplicateNodes();
    }

    public function deduplicateNodes(): void
    {
        $this->types = $this->uniqueNodes($this->types);
    }

    public function write(WritingContext $context): string
    {
        return implode(' & ', array_map(
            fn (TypeScriptNode $type) => $type->write($context),
            $this->types
        ));
    }
}
