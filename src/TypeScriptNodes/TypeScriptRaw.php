<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Attributes\AdditionalImport;
use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptRaw implements TypeScriptNode
{
    /**
     * @param array<AdditionalImport> $additionalImports
     */
    public function __construct(
        public string $typeScript,
        public array $additionalImports = [],
    ) {
    }

    public function write(WritingContext $context): string
    {
        if ($this->additionalImports === []) {
            return $this->typeScript;
        }

        $result = $this->typeScript;

        foreach ($this->additionalImports as $import) {
            foreach ($import->getReferenceKeys() as $name => $referenceKey) {
                $resolved = $context->resolvedReferenceMap[$referenceKey] ?? $name;

                $result = preg_replace(
                    '/(?<!\w)(?<!\.)' . preg_quote($name, '/') . '(?!\w)(?!\.)/',
                    $resolved,
                    $result
                );
            }
        }

        return $result;
    }
}
