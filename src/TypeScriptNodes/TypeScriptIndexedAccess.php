<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Data\VisitorProfile;
use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptIndexedAccess implements TypeScriptNode, TypeScriptVisitableNode
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

    public function visitorProfile(): VisitorProfile
    {
        return VisitorProfile::create()->single('node')->iterable('segments');
    }
}
