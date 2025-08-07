<?php

namespace Spatie\TypeScriptTransformer\Data;

use Spatie\TypeScriptTransformer\References\Reference;
use Spatie\TypeScriptTransformer\Support\ImportName;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptImport;

class ImportLocation
{
    /**
     * @param  array<ImportName>  $importNames
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

    public function toTypeScriptNode(): TypeScriptImport
    {
        return new TypeScriptImport($this->relativePath, $this->importNames);
    }
}
