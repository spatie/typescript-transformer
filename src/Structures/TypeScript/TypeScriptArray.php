<?php

namespace Spatie\TypeScriptTransformer\Structures\TypeScript;

class TypeScriptArray implements TypeScriptStructure
{
    public function __construct(
        public ?TypeScriptType $type
    ) {
    }

    public function __toString(): string
    {
        if ($this->type === null) {
            return 'Array';
        }

        return "Array<{$this->type}>";
    }

    public function replaceReference(string $replaceSymbol, string $replacement): void
    {
        $this->type->replaceReference($replaceSymbol, $replacement);
    }
}
