<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\References\Reference;
use Spatie\TypeScriptTransformer\Support\WritingContext;
use Spatie\TypeScriptTransformer\Transformed\Transformed;

class TypeReference implements TypeScriptExportableNode, TypeScriptNode
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

    public function write(WritingContext $context): string
    {
        if($this->referenced === null) {
            return 'undefined';
        }

        return ($context->referenceWriter)($this->reference);
    }

    public function getExportedName(): string
    {
        return $this->referenced->getName();
    }
}
