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
        return (str_contains($this->name, '.') || str_contains($this->name, '\\')) ? "'{$this->name}'" : $this->name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
