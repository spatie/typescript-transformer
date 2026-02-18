<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;
use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptIndexedAccess implements TypeScriptNode
{
    /**
     * @param  array<TypeScriptNode>  $segments
     */
    public function __construct(
        #[NodeVisitable]
        public TypeScriptIdentifier|TypeScriptReference $node,
        #[NodeVisitable]
        public array $segments,
    ) {
    }

    public function write(WritingContext $context): string
    {
        $segments = array_map(
            fn (TypeScriptNode $segment) => "[{$segment->write($context)}]",
            $this->segments
        );

        return "{$this->node->write($context)}".implode('', $segments);
    }
}
