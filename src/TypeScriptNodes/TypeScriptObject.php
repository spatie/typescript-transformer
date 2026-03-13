<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;
use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptObject implements TypeScriptNode
{
    /**
     * @param  array<TypeScriptProperty>  $properties
     */
    public function __construct(
        #[NodeVisitable]
        public array $properties
    ) {
    }

    public function write(WritingContext $context): string
    {
        if (empty($this->properties)) {
            return 'object';
        }

        $output = '{'."\n";

        foreach ($this->properties as $property) {
            $output .= $property->write($context).','."\n";
        }

        return $output.'}';
    }
}
