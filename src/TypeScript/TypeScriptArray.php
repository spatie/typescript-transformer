<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\VisitorProfile;
use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptArray implements TypeScriptNode, TypeScriptVisitableNode
{
    /**
     * @param  TypeScriptNode[]  $types
     */
    public function __construct(
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

    public function visitorProfile(): VisitorProfile
    {
        return VisitorProfile::create()->iterable('types');
    }
}
