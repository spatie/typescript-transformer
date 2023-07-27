<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptIndexedAccess implements TypeScriptNode, TypeScriptNodeWithChildren
{
    /**
     * @param  array<TypeScriptNode>  $segments
     */
    public function __construct(
        public TypeScriptIdentifier|TypeReference $node,
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

    public function children(): array
    {
        return [$this->node, ...$this->segments];
    }
}
