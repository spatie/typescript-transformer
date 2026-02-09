<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;
use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptExport implements TypeScriptNode
{
    public function __construct(
        #[NodeVisitable]
        public (TypeScriptNamedNode&TypeScriptNode)|TypeScriptForwardingNamedNode $node,
    ) {
    }

    public function write(WritingContext $context): string
    {
        return "export {$this->node->write($context)}";
    }
}
