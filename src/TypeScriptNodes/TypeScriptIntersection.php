<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Support\VisitorProfile;
use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptIntersection implements TypeScriptNode, TypeScriptVisitableNode
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

    public function visitorProfile(): VisitorProfile
    {
        return VisitorProfile::create()->iterable('types');
    }
}
