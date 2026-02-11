<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;
use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptInterface implements TypeScriptForwardingNamedNode, TypeScriptNode
{
    #[NodeVisitable]
    public TypeScriptIdentifier $name;

    /**
     * @param  array<TypeScriptProperty>  $properties
     * @param  array<TypeScriptMethodSignature>  $methods
     */
    public function __construct(
        TypeScriptIdentifier|string $name,
        #[NodeVisitable]
        public array $properties,
        #[NodeVisitable]
        public array $methods,
    ) {
        $this->name = is_string($name)
            ? new TypeScriptIdentifier($name)
            : $name;
    }

    public function write(WritingContext $context): string
    {
        $combined = [...$this->properties, ...$this->methods];

        $items = array_reduce(
            $combined,
            fn (string $carry, TypeScriptProperty|TypeScriptMethodSignature $item) => $carry.$item->write($context).PHP_EOL,
            empty($combined) ? '' : PHP_EOL
        );

        return "interface {$this->name->write($context)} {{$items}}";
    }

    public function getForwardedNamedNode(): TypeScriptNamedNode|TypeScriptForwardingNamedNode
    {
        return $this->name;
    }
}
