<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptIdentifier implements TypeScriptExportableNode, TypeScriptNode
{
    public function __construct(
        public string $name,
    ) {
    }

    public function write(WritingContext $context): string
    {
        return (str_contains($this->name, '.') || str_contains($this->name, '\\')) ? "'{$this->name}'" : $this->name;
    }

    public function getExportedName(): string
    {
        return $this->name;
    }
}
