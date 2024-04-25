<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\VisitorProfile;
use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptExport implements TypeScriptNode, TypeScriptVisitableNode
{
    public function __construct(
        public TypeScriptExportableNode|TypeScriptForwardingExportableNode $node,
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
