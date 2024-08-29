<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Support\VisitorProfile;
use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptExport implements TypeScriptNode, TypeScriptVisitableNode
{
    public function __construct(
        public TypeScriptNamedNode|TypeScriptForwardingNamedNode $node,
    ) {
    }

    public function write(WritingContext $context): string
    {
        return "export {$this->node->write($context)}";
    }

    public function visitorProfile(): VisitorProfile
    {
        return VisitorProfile::create()->single('node');
    }
}
