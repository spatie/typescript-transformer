<?php

namespace Spatie\TypeScriptTransformer\Support;

use Spatie\TypeScriptTransformer\References\Reference;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptImport;

class ImportLocation
{
    /**
     * @param array<ImportName> $importNames
     */
    public function __construct(
        protected string $relativePath,
        protected array $importNames = [],
    ) {
    }

    public function addName(ImportName $name): void
    {
        $this->importNames[] = $name;
    }

    public function getAliasOrNameForReference(Reference $reference): ?string
    {
        foreach ($this->importNames as $importName) {
            if ($importName->reference->getKey() === $reference->getKey()) {
                return $importName->alias ?? $importName->name;
            }
        }

        return null;
    }

    public function toTypeScriptNode(): ?TypeScriptImport
    {
        if ($this->relativePath === null) {
            // current path
            return null;
        }

        $names = array_unique(array_map(
            fn (ImportName $name) => (string) $name,
            $this->importNames,
        ));

        return new TypeScriptImport($this->relativePath, $names);
    }
}
