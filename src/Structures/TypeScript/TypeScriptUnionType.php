<?php

namespace Spatie\TypeScriptTransformer\Structures\TypeScript;

class TypeScriptUnionType extends TypeScriptType
{
    /**
     * @param \Spatie\TypeScriptTransformer\Structures\TypeScript\TypeScriptSingleType[] $types
     */
    public function __construct(
        public array $types,
    ) {
    }

    public function __toString()
    {
        return implode(' | ', $this->types);
    }

    public function replaceReference(string $replaceSymbol, string $replacement): void
    {
        foreach ($this->types as $type) {
            $type->replaceReference($replaceSymbol, $replacement);
        }
    }
}
