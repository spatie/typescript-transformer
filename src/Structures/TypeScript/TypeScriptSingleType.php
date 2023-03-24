<?php

namespace Spatie\TypeScriptTransformer\Structures\TypeScript;

class TypeScriptSingleType extends TypeScriptType
{
    public function __construct(
        public string $type,
    ) {
    }

    public function replaceReference(
        string $replaceSymbol,
        string $replacement
    ): void {
        $this->type = str_replace(
            $replaceSymbol,
            $replacement,
            $this->type
        );
    }

    public function __toString()
    {
        return $this->type;
    }
}
