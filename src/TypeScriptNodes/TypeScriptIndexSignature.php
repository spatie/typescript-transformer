<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Data\VisitorProfile;
use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptIndexSignature implements TypeScriptNode, TypeScriptVisitableNode
{
    public function __construct(
        public TypeScriptNode $type,
        public string $name = 'index',
    ) {
    }

    public function write(WritingContext $context): string
    {
        return "[{$this->name}: {$this->type->write($context)}]";
    }

    public function visitorProfile(): VisitorProfile
    {
        return VisitorProfile::create()->single('type');
    }
}
