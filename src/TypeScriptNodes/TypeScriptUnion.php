<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Closure;
use Spatie\TypeScriptTransformer\Support\VisitorProfile;
use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptUnion implements TypeScriptNode, TypeScriptVisitableNode
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
        return implode(' | ', array_map(
            fn (TypeScriptNode $type) => $type->write($context),
            $this->types
        ));
    }

    public function contains(Closure $closure): bool
    {
        foreach ($this->types as $type) {
            if ($closure($type)) {
                return true;
            }
        }

        return false;
    }

    public function visitorProfile(): VisitorProfile
    {
        return VisitorProfile::create()->iterable('types');
    }
}
