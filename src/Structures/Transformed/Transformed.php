<?php

namespace Spatie\TypeScriptTransformer\Structures\Transformed;

use Spatie\TypeScriptTransformer\Structures\TypeReference;
use Spatie\TypeScriptTransformer\Structures\TypeReferencesCollection;
use Spatie\TypeScriptTransformer\Structures\TypeScript\TypeScriptStructure;

class Transformed
{
    public string $cachedInlineResolvedStructure;

    public function __construct(
        public TypeReference $name,
        public TypeScriptStructure $structure,
        public TypeReferencesCollection $typeReferences = new TypeReferencesCollection(),
        public bool $inline = false,
    ) {
    }

    public function replaceTypeReference(TypeReference $typeReference, string $replacement): void
    {
        $this->structure->replaceReference($typeReference->replaceSymbol(), $replacement);
    }

    public function toString(): string
    {
        if ($this->inline && isset($this->cachedInlineResolvedStructure)) {
            return $this->cachedInlineResolvedStructure;
        }

        if ($this->inline) {
            return $this->cachedInlineResolvedStructure = (string) $this->structure;
        }

        return $this->structure;
    }
}
