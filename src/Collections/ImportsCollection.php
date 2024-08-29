<?php

namespace Spatie\TypeScriptTransformer\Collections;

use IteratorAggregate;
use Spatie\TypeScriptTransformer\Data\ImportLocation;
use Spatie\TypeScriptTransformer\References\Reference;
use Spatie\TypeScriptTransformer\Support\ImportName;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptImport;
use Traversable;

class ImportsCollection implements IteratorAggregate
{
    /**
     * @param  array<string, ImportLocation>  $imports
     */
    public function __construct(
        protected array $imports = [],
    ) {
    }

    public function add(string $relativePath, ImportName $name): void
    {
        if (! array_key_exists($relativePath, $this->imports)) {
            $this->imports[$relativePath] = new ImportLocation($relativePath);
        }

        $this->imports[$relativePath]->addName($name);
    }

    public function getAliasOrNameForReference(Reference $reference): ?string
    {
        foreach ($this->imports as $import) {
            if ($aliasOrName = $import->getAliasOrNameForReference($reference)) {
                return $aliasOrName;
            }
        }

        return null;
    }

    public function hasReferenceImported(Reference $reference): bool
    {
        return $this->getAliasOrNameForReference($reference) !== null;
    }

    public function isEmpty(): bool
    {
        return empty($this->imports);
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->imports);
    }

    /**
     * @return array<TypeScriptImport>
     */
    public function getTypeScriptNodes(): array
    {
        return array_values(array_map(
            fn (ImportLocation $import) => $import->toTypeScriptNode(),
            $this->imports,
        ));
    }

    /** @return array<ImportLocation> */
    public function toArray(): array
    {
        return array_values($this->imports);
    }
}
