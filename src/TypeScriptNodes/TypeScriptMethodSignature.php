<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;
use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptMethodSignature implements TypeScriptNode
{
    #[NodeVisitable]
    public TypeScriptIdentifier $name;

    /**
     * @param  array<TypeScriptParameter>  $parameters
     */
    public function __construct(
        TypeScriptIdentifier|string $name,
        #[NodeVisitable]
        public array $parameters,
        #[NodeVisitable]
        public TypeScriptNode $returnType,
    ) {
        $this->name = is_string($name) ? new TypeScriptIdentifier($name) : $name;
    }

    public function write(WritingContext $context): string
    {
        $parameters = implode(', ', array_map(
            fn (TypeScriptParameter $parameter) => $parameter->write($context),
            $this->parameters
        ));

        return "{$this->name->write($context)}({$parameters}): {$this->returnType->write($context)};";
    }
}
