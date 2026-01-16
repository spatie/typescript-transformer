<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Data\VisitorProfile;
use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptExport implements TypeScriptNode, TypeScriptVisitableNode
{
    public function __construct(
        public (TypeScriptNamedNode&TypeScriptNode)|TypeScriptForwardingNamedNode $node,
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
