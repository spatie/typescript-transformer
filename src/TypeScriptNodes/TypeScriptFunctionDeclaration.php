<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;
use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptFunctionDeclaration implements TypeScriptForwardingNamedNode, TypeScriptNode
{
    #[NodeVisitable]
    public TypeScriptGeneric|TypeScriptIdentifier $identifier;

    public function __construct(
        TypeScriptGeneric|TypeScriptIdentifier|string $identifier,
        #[NodeVisitable]
        public array $parameters,
        #[NodeVisitable]
        public TypeScriptNode $returnType,
        #[NodeVisitable]
        public TypeScriptNode $body,
    ) {
        $this->identifier = is_string($identifier)
            ? new TypeScriptIdentifier($identifier)
            : $identifier;
    }

    public function write(WritingContext $context): string
    {
        $parameters = implode(', ', array_map(fn (TypeScriptNode $parameter) => $parameter->write($context), $this->parameters));

        return "function {$this->identifier->write($context)}({$parameters}): {$this->returnType->write($context)} {\n{$this->body->write($context)}\n}";
    }

    public function getForwardedNamedNode(): TypeScriptNamedNode|TypeScriptForwardingNamedNode
    {
        return $this->identifier;
    }
}
