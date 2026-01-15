<?php

namespace Spatie\TypeScriptTransformer\Collections;

use Closure;
use Spatie\TypeScriptTransformer\References\Reference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptImport;

class ModuleImportsCollection
{
    /**
     * @param array<string, array{
     *     path: string,
     *     segments: array<string, array{name: string, reference: string, alias: ?string}>
     * }> $imports
     */
    public function __construct(
        protected array $imports = [],
    ) {
    }

    public function add(string $relativePath, string $name, string|Reference $reference): void
    {
        if (! array_key_exists($relativePath, $this->imports)) {
            $this->imports[$relativePath] = [
                'path' => $relativePath,
                'segments' => [],
            ];
        }

        $reference = $reference instanceof Reference ? $reference->getKey() : $reference;

        if (array_key_exists($reference, $this->imports[$relativePath]['segments'])) {
            return;
        }

        $this->imports[$relativePath]['segments'][$reference] = [
            'name' => $name,
            'reference' => $reference,
            'alias' => null,
        ];
    }


    public function hasReferenceImported(Reference|string $reference): bool
    {
        $reference = $reference instanceof Reference ? $reference->getKey() : $reference;

        foreach ($this->imports as $import) {
            if (array_key_exists($reference, $import['segments'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $usedNamesInModule
     * @param Closure(string, array<string>):string|null $moduleImportNameResolver
     */
    public function aliasDuplicates(
        array $usedNamesInModule,
        ?Closure $moduleImportNameResolver = null,
    ): void {
        foreach ($this->imports as ['path' => $path, 'segments' => $segments]) {
            foreach ($segments as ['name' => $name, 'reference' => $reference]) {
                $importedName = $this->resolveImportedName(
                    $usedNamesInModule,
                    $name,
                    $moduleImportNameResolver,
                );

                if ($importedName !== $name) {
                    $this->imports[$path]['segments'][$reference]['alias'] = $importedName;
                }

                $usedNamesInModule[] = $importedName;
            }
        }
    }

    /**
     * @return array<string, string>
     */
    public function getReferenceMap(): array
    {
        $referenceMap = [];

        foreach ($this->imports as ['segments' => $segments]) {
            foreach ($segments as ['name' => $name, 'reference' => $reference, 'alias' => $alias]) {
                $referenceMap[$reference] = $alias ?? $name;
            }
        }

        return $referenceMap;
    }

    /**
     * @return array<TypeScriptImport>
     */
    public function getTypeScriptNodes(): array
    {
        return array_values(array_map(
            fn (array $import) => new TypeScriptImport(
                str_replace(DIRECTORY_SEPARATOR, '/', $import['path']),
                $import['segments']
            ),
            $this->imports,
        ));
    }

    public function getImports(): array
    {
        return $this->imports;
    }

    /**
     * @param array<string> $usedNamesInModule
     * @param Closure(string, array<string>):string|null $moduleImportNameResolver
     */
    protected function resolveImportedName(
        array $usedNamesInModule,
        string $name,
        ?Closure $moduleImportNameResolver,
    ): string {
        if ($moduleImportNameResolver) {
            return ($moduleImportNameResolver)($name, $usedNamesInModule);
        }

        if (! in_array($name, $usedNamesInModule)) {
            return $name;
        }

        if (! in_array("{$name}Import", $usedNamesInModule)) {
            return "{$name}Import";
        }

        $counter = 2;

        while (in_array("{$name}Import{$counter}", $usedNamesInModule)) {
            $counter++;
        }

        return "{$name}Import{$counter}";
    }
}
