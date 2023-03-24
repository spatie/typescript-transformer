<?php

namespace Spatie\TypeScriptTransformer\Structures\TypeScript;

class TypeScriptMethod implements TypeScriptStructure
{
    /**
     * @param \Spatie\TypeScriptTransformer\Structures\TypeScript\TypeScriptParameter[] $parameters
     */
    public function __construct(
        public string $name,
        public array $parameters,
        public TypeScriptType $returnType,
    ) {
    }

    public function __toString()
    {
        $parameters = implode(', ', $this->parameters);

        return "{$this->name}({$parameters}): {$this->returnType};";
    }

    public function replaceReference(string $replaceSymbol, string $replacement): void
    {
        foreach ($this->parameters as $parameter) {
            $parameter->replaceReference($replaceSymbol, $replacement);
        }

        $this->returnType->replaceReference($replaceSymbol, $replacement);
    }
}
