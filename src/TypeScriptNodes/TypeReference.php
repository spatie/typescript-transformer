<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\References\Reference;
use Spatie\TypeScriptTransformer\Support\WritingContext;
use Spatie\TypeScriptTransformer\Transformed\Transformed;

class TypeReference implements TypeScriptNamedNode, TypeScriptNode
{
    public static function referencingPhpClass(string $class): self
    {
        return new self(new ClassStringReference($class));
    }

    public function __construct(
        public Reference $reference,
        public ?Transformed $referenced = null,
    ) {
    }

    public function connect(Transformed $transformed): void
    {
        $this->referenced = $transformed;
    }

    public function unconnect(): void
    {
        $this->referenced = null;
    }

    public function write(WritingContext $context): string
    {
        if ($this->referenced === null) {
            return 'undefined';
        }

        $key = $this->reference->getKey();

        return $context->nameMap[$key] ?? $this->referenced->getName();
    }

    public function getName(): string
    {
        return $this->referenced->getName();
    }
}
