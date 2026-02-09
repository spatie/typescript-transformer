<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;
use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptInterfaceMethod implements TypeScriptNode
{
    /**
     * @param  array<TypeScriptParameter>  $parameters
     */
    public function __construct(
        public string $name,
        #[NodeVisitable]
        public array $parameters,
        #[NodeVisitable]
        public TypeScriptNode $returnType,
    ) {
    }

    public function write(WritingContext $context): string
    {
        $parameters = implode(', ', array_map(
            fn (TypeScriptParameter $parameter) => $parameter->write($context),
            $this->parameters
        ));

        return "{$this->name}({$parameters}): {$this->returnType->write($context)};";
    }
}
