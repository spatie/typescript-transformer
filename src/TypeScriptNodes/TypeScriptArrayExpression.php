<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;
use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptArrayExpression implements TypeScriptNode
{
    /**
     * @param  array<TypeScriptNode>  $elements
     */
    public function __construct(
        #[NodeVisitable]
        public array $elements = [],
    ) {
    }

    public function write(WritingContext $context): string
    {
        if (empty($this->elements)) {
            return '[]';
        }

        $elements = implode(', ', array_map(
            fn (TypeScriptNode $element) => $element->write($context),
            $this->elements,
        ));

        return "[{$elements}]";
    }
}
