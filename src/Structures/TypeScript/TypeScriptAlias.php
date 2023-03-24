<?php

namespace Spatie\TypeScriptTransformer\Structures\TypeScript;

class TypeScriptAlias implements TypeScriptStructure
{
    public function __construct(
        public string $name,
        public TypeScriptType|TypeScriptObject|TypeScriptRaw $alias,
    ) {
    }

    public function __toString()
    {
        return "type {$this->name} = {$this->alias};";
    }

    public function replaceReference(string $replaceSymbol, string $replacement): void
    {
        $this->alias->replaceReference($replaceSymbol, $replacement);
    }
}
