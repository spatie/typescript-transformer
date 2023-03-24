<?php

namespace Spatie\TypeScriptTransformer\Structures\TypeScript;

use Spatie\TypeScriptTransformer\Structures\TypeReferencesCollection;

class TypeScriptInterface implements TypeScriptStructure
{
    /**
     * @param array<\Spatie\TypeScriptTransformer\Structures\TypeScript\TypeScriptProperty> $properties
     * @param array<\Spatie\TypeScriptTransformer\Structures\TypeScript\TypeScriptMethod> $methods
     */
    public function __construct(
        public string $name,
        public array $properties,
        public array $methods,
    ) {
    }

    public function __toString()
    {
        $combined = [...$this->properties, ...$this->methods];

        $items = array_reduce(
            $combined,
            fn(string $carry, TypeScriptProperty|TypeScriptMethod $item) => $carry . $item . PHP_EOL,
            empty($combined) ? '' : PHP_EOL
        );

        return "interface {$this->name} {{$items}}";
    }

    public function replaceReference(string $replaceSymbol, string $replacement): void
    {
        foreach ($this->properties as $property) {
            $property->replaceReference($replaceSymbol, $replacement);
        }

        foreach ($this->methods as $method) {
            $method->replaceReference($replaceSymbol, $replacement);
        }
    }
}
