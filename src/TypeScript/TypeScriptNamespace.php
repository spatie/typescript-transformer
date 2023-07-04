<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptNamespace implements TypeScriptNode
{
    /**
     * @param  array<TypeScriptNode>  $types
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

        $output .= '}'.PHP_EOL;

        return $output;
    }
}
