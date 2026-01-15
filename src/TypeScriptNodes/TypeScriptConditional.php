<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Data\VisitorProfile;
use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptConditional implements TypeScriptNode, TypeScriptVisitableNode
{
    public function __construct(
        public TypeScriptNode $condition,
        public TypeScriptNode $ifTrue,
        public TypeScriptNode $ifFalse,
    ) {
    }

    public function write(WritingContext $context): string
    {
        return "{$this->condition->write($context)} ? {$this->ifTrue->write($context)} : {$this->ifFalse->write($context)}";
    }

    public function visitorProfile(): VisitorProfile
    {
        return VisitorProfile::create()->single('condition', 'ifTrue', 'ifFalse');
    }
}
