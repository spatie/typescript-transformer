<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Attributes\AdditionalImport;
use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;
use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\References\Reference;

class TypeScriptRaw implements TypeScriptNode
{
    /** @var array<string, TypeScriptReference> */
    #[NodeVisitable]
    public array $references = [];

    /**
     * @param array<AdditionalImport> $additionalImports
     * @param array<string, string|Reference> $references
     */
    public function __construct(
        public string $typeScript,
        public array $additionalImports = [],
        array $references = [],
    ) {
        foreach ($references as $name => $reference) {
            if (is_string($reference)) {
                $reference = new ClassStringReference($reference);
            }

            $this->references[$name] = new TypeScriptReference($reference);
        }
    }

    public function write(WritingContext $context): string
    {
        $result = $this->typeScript;

        foreach ($this->references as $name => $reference) {
            $resolved = $reference->write($context);

            $result = str_replace("%{$name}%", $resolved, $result);
        }

        if ($this->additionalImports === []) {
            return $result;
        }

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
