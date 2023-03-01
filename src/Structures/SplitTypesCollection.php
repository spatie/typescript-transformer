<?php

namespace Spatie\TypeScriptTransformer\Structures;

class SplitTypesCollection
{
    /**
     * @param array<string, \Spatie\TypeScriptTransformer\Structures\TypeImport> $imports
     */
    public function __construct(
        public string $path,
        public TypesCollection $types,
        public array $imports,
    ) {
    }

    public function addImport(TypeImport ...$imports): void
    {
        foreach ($imports as $import) {
            if (! array_key_exists($import->file, $this->imports)) {
                $this->imports[$import->file] = new TypeImport($import->file, []);
            }

            array_push(
                $this->imports[$import->file]->types,
                ...$import->types
            );
        }
    }

    public function cleanupImports(): void
    {
        foreach ($this->imports as $import) {
            $import->types = array_unique($import->types);
        }
    }
}
