<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;
use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptFunctionDefinition implements TypeScriptForwardingNamedNode, TypeScriptNode
{
    public function __construct(
        #[NodeVisitable]
        public TypeScriptGeneric|TypeScriptIdentifier $identifier,
        #[NodeVisitable]
        public array $parameters,
        #[NodeVisitable]
        public TypeScriptNode $returnType,
        #[NodeVisitable]
        public TypeScriptNode $body,
    ) {
    }

    public function write(WritingContext $context): string
    {
        $parameters = implode(', ', array_map(fn (TypeScriptNode $parameter) => $parameter->write($context), $this->parameters));

        return "function {$this->identifier->write($context)}({$parameters}): {$this->returnType->write($context)} {
            {$this->body->write($context)}
        }";
    }

    public function getForwardedNamedNode(): TypeScriptNamedNode|TypeScriptForwardingNamedNode
    {
        return $this->identifier;
    }
}
