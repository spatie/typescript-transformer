<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptFunctionDefinition implements TypeScriptNode, TypeScriptNodeWithChildren
{
    public function __construct(
        public TypeScriptNode $identifier,
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

    public function children(): array
    {
        return [
            $this->identifier,
            ...$this->parameters ?? [],
            $this->returnType,
            $this->body,
        ];
    }
}
