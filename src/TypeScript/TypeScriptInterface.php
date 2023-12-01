<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\VisitorProfile;
use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptInterface implements TypeScriptForwardingExportableNode, TypeScriptNode, TypeScriptVisitableNode
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

    public function visitorProfile(): VisitorProfile
    {
        return VisitorProfile::create()
            ->single('name')
            ->iterable('properties')
            ->iterable('methods');
    }

    public function getForwardedExportableNode(): TypeScriptExportableNode|TypeScriptForwardingExportableNode
    {
        return $this->name;
    }
}
