<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptInterface implements TypeScriptNode, TypeScriptNodeWithChildren
{
    /**
     * @param  array<TypeScriptProperty>  $properties
     * @param  array<TypeScriptMethod>  $methods
     */
    public function __construct(
        public TypeScriptIdentifier $name,
        public array $properties,
        public array $methods,
    ) {
    }

    public function write(WritingContext $context): string
    {
        $combined = [...$this->properties, ...$this->methods];

        $items = array_reduce(
            $combined,
            fn (string $carry, TypeScriptProperty|TypeScriptMethod $item) => $carry.$item->write($context).PHP_EOL,
            empty($combined) ? '' : PHP_EOL
        );

        return "interface {$this->name->write($context)} {{$items}}";
    }

    public function children(): array
    {
        return [...$this->properties, ...$this->methods];
    }
}
