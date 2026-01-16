<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Data\VisitorProfile;
use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptFunctionDefinition implements TypeScriptForwardingNamedNode, TypeScriptNode, TypeScriptVisitableNode
{
    public function __construct(
        public TypeScriptGeneric|TypeScriptIdentifier $identifier,
        public array $parameters,
        public TypeScriptNode $returnType,
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

    public function visitorProfile(): VisitorProfile
    {
        return VisitorProfile::create()->single('identifier', 'returnType', 'body')->iterable('parameters');
    }

    public function getForwardedNamedNode(): TypeScriptNamedNode|TypeScriptForwardingNamedNode
    {
        return $this->identifier;
    }
}
