<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;
use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptInterface implements TypeScriptForwardingNamedNode, TypeScriptNode
{
    /**
     * @param  array<TypeScriptProperty>  $properties
     * @param  array<TypeScriptInterfaceMethod>  $methods
     */
    public function __construct(
        #[NodeVisitable]
        public TypeScriptIdentifier $name,
        #[NodeVisitable]
        public array $properties,
        #[NodeVisitable]
        public array $methods,
    ) {
    }

    public function write(WritingContext $context): string
    {
        $combined = [...$this->properties, ...$this->methods];

        $items = array_reduce(
            $combined,
            fn (string $carry, TypeScriptProperty|TypeScriptInterfaceMethod $item) => $carry.$item->write($context).PHP_EOL,
            empty($combined) ? '' : PHP_EOL
        );

        return "interface {$this->name->write($context)} {{$items}}";
    }

    public function getForwardedNamedNode(): TypeScriptNamedNode|TypeScriptForwardingNamedNode
    {
        return $this->name;
    }
}
