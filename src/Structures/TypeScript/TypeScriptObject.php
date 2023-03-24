<?php

namespace Spatie\TypeScriptTransformer\Structures\TypeScript;

class TypeScriptObject implements TypeScriptStructure
{
    /**
     * @param array<\Spatie\TypeScriptTransformer\Structures\TypeScript\TypeScriptProperty> $properties
     */
    public function __construct(
        public array $properties,
    ) {
    }

    public function __toString()
    {
        $properties = array_reduce(
            $this->properties,
            fn (string $carry, TypeScriptProperty $property) => $carry . $property . PHP_EOL,
            empty($this->properties) ? '' : PHP_EOL
        );

        return "{{$properties}}";
    }

    public function replaceReference(string $replaceSymbol, string $replacement): void
    {
        foreach ($this->properties as $property) {
            $property->replaceReference($replaceSymbol, $replacement);
        }
    }
}
