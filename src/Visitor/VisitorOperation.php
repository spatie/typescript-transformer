<?php

namespace Spatie\TypeScriptTransformer\Visitor;

use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;

class VisitorOperation
{
    public static function remove(): self
    {
        return new self(VisitorOperationType::Remove);
    }

    public static function replace(TypeScriptNode $replacement): self
    {
        return new self(VisitorOperationType::Replace, $replacement);
    }

    public static function keep(): self
    {
        return new self(VisitorOperationType::Keep);
    }

    protected function __construct(
        public VisitorOperationType $type,
        public ?TypeScriptNode $node = null,
    ) {

    }
}
