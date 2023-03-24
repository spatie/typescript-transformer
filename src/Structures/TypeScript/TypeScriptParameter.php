<?php

namespace Spatie\TypeScriptTransformer\Structures\TypeScript;

class TypeScriptParameter implements TypeScriptStructure
{
    public function __construct(
        public string $name,
        public TypeScriptType $type,
        public bool $optional,
    ) {
    }

    public function replaceReference(
        string $replaceSymbol,
        string $replacement
    ): void {
        $this->type->replaceReference($replaceSymbol, $replacement);
    }

    public function __toString()
    {
        $name = ! preg_match('/^[$_a-zA-Z][$_a-zA-Z0-9]*$/', $this->name)
            ? "'{$this->name}'"
            : $this->name;

        return $this->optional
            ? "{$name}?: {$this->type}"
            : "{$name}: {$this->type}";
    }
}
