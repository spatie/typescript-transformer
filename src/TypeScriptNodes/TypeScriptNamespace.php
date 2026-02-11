<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;
use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\Transformed\Transformed;

class TypeScriptNamespace implements TypeScriptNode
{
    /**
     * @param array<TypeScriptNode|Transformed> $types
     * @param array<TypeScriptNamespace> $children
     */
    public function __construct(
        public string $name,
        #[NodeVisitable]
        public array $types,
        #[NodeVisitable]
        public array $children = [],
        public bool $declare = true,
    ) {
    }

    public function write(WritingContext $context): string
    {
        $prefix = $this->declare ? 'declare namespace' : 'namespace';

        $output = "{$prefix} {$this->name} {".PHP_EOL;

        foreach ($this->types as $type) {
            $output .= $type->write($context).PHP_EOL;
        }

        foreach ($this->children as $child) {
            $output .= $child->write($context).PHP_EOL;
        }

        $output .= '}';

        return $output;
    }
}
