<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Support\WritingContext;
use Spatie\TypeScriptTransformer\Transformed\Transformed;

class TypeScriptNamespace implements TypeScriptNode
{
    /**
     * @param  array<TypeScriptNode|Transformed>  $types
     */
    public function __construct(
        public array $namespaceSegments,
        public array $types,
    ) {
    }

    public function write(WritingContext $context): string
    {
        $output = 'declare namespace '.implode('.', $this->namespaceSegments).'{'.PHP_EOL;

        foreach ($this->types as $type) {
            $output .= $type->write($context).PHP_EOL;
        }

        $output .= '}';

        return $output;
    }
}
