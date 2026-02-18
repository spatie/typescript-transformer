<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\References\Reference;
use Spatie\TypeScriptTransformer\Transformed\Transformed;

class TypeScriptReference implements TypeScriptNamedNode, TypeScriptNode
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

        return $context->resolveReference($this->reference->getKey());
    }

    public function getName(): string
    {
        return $this->referenced->getName();
    }
}
