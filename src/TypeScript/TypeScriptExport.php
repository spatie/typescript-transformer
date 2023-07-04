<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptExport implements TypeScriptNode, TypeScriptNodeWithChildren
{
    public function __construct(
        public TypeScriptNode $node,
    ) {
    }

    public function write(WritingContext $context): string
    {
        return "export {$this->node->write($context)}".PHP_EOL;
    }

    public function children(): array
    {
        return [$this->node];
    }
}
