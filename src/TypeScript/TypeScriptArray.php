<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptArray implements TypeScriptNode, TypeScriptNodeWithChildren
{
    public function __construct(
        public ?TypeScriptNode $type
    ) {
    }

    public function write(WritingContext $context): string
    {
        return $this->type
            ? "Array<{$this->type->write($context)}>"
            : 'Array';
    }

    public function children(): array
    {
        return $this->type ? [$this->type] : [];
    }
}
