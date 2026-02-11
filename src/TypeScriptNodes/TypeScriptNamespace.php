<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;
use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\Transformed\Transformed;

class TypeScriptNamespace implements TypeScriptNode
{
    /**
     * @param  array<TypeScriptNode|Transformed>  $types
     */
    public function __construct(
        public array $namespaceSegments,
        #[NodeVisitable]
        public array $types,
        public bool $declare = true,
    ) {
    }

    public function write(WritingContext $context): string
    {
        $prefix = $this->declare ? 'declare namespace' : 'namespace';

        $output = $prefix.' '.implode('.', $this->namespaceSegments).'{'.PHP_EOL;

        foreach ($this->types as $type) {
            $output .= $type->write($context).PHP_EOL;
        }

        $output .= '}';

        return $output;
    }
}
