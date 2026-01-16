<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptIdentifier implements TypeScriptNamedNode, TypeScriptNode
{
    public function __construct(
        public string $name,
    ) {
    }

    public function write(WritingContext $context): string
    {
        return $this->isValidIdentifier($this->name) ? $this->name : "'$this->name'";
    }

    private function isValidIdentifier(string $name): bool
    {
        return preg_match('/^[a-zA-Z_$][a-zA-Z0-9_$]*$/', $name) === 1;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
