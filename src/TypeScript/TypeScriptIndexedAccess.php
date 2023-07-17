<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptIndexedAccess implements TypeScriptNode, TypeScriptNodeWithChildren
{
    /**
     * @param TypeScriptIdentifier $node
     * @param array<TypeScriptNode> $segments
     */
    public function __construct(
        public TypeScriptIdentifier $node,
        public array $segments,
    ) {
    }

    public function write(WritingContext $context): string
    {
        $segments = array_map(
            fn(TypeScriptNode $segment) => "[{$segment->write($context)}]",
            $this->segments
        );

        return "{$this->node->write($context)}" . implode('', $segments);
    }

    public function children(): array
    {
        return [$this->node, ...$this->segments];
    }
}
