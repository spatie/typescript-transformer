<?php

namespace Spatie\TypeScriptTransformer\Structures\TypeScript;

class TypeScriptRaw implements TypeScriptStructure
{
    public function __construct(
        public string $raw
    ) {
    }

    public function __toString()
    {
        return $this->raw;
    }

    public function replaceReference(string $replaceSymbol, string $replacement): void
    {
        $this->raw = str_replace(
            $replaceSymbol,
            $replacement,
            $this->raw
        );
    }
}
