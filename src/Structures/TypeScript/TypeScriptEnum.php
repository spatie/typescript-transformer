<?php

namespace Spatie\TypeScriptTransformer\Structures\TypeScript;

class TypeScriptEnum implements TypeScriptStructure
{
    public function __construct(
        public string $name,
        public array $options,
    ) {
    }

    public function __toString()
    {
        $options = collect($this->options)
            ->map(fn (mixed $value) => is_string($value) ? "'{$value}'" : $value)
            ->map(fn (mixed $value, $key) => "{$key} = {$value}")
            ->values()
            ->implode(', ');

        return "enum {$this->name} {{$options}}";
    }

    public function replaceReference(string $replaceSymbol, string $replacement): void
    {
        // no replacing possible
    }
}
