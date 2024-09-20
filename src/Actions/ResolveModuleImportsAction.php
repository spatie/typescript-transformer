<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Closure;
use Spatie\TypeScriptTransformer\Collections\ImportsCollection;
use Spatie\TypeScriptTransformer\Support\ImportName;
use Spatie\TypeScriptTransformer\Support\Location;
use Spatie\TypeScriptTransformer\Transformed\Transformed;

class ResolveModuleImportsAction
{
    /**
     * @param  Closure(array<string>,string):string|null  $alternativeNamesResolver
     */
    public function __construct(
        protected ResolveRelativePathAction $resolveRelativePathAction = new ResolveRelativePathAction(),
        protected ?Closure $alternativeNamesResolver = null,
    ) {
    }

    public function execute(
        Location $location,
    ): ImportsCollection {
        $collection = new ImportsCollection();

        $usedNamesInModule = array_values(
            array_map(fn (Transformed $transformed) => $transformed->getName(), $location->transformed)
        );

        foreach ($location->transformed as $transformedItem) {
            foreach ($transformedItem->references as $referencedTransformed => $typeReferences) {
                if ($referencedTransformed->location === $location->segments) {
                    continue;
                }

                if ($collection->hasReferenceImported($referencedTransformed->reference)) {
                    continue;
                }

                $name = $referencedTransformed->getName();

                $resolveImportedName = $this->resolveImportedName($usedNamesInModule, $name);

                $usedNamesInModule[] = $resolveImportedName;

                $importName = new ImportName(
                    $name,
                    $referencedTransformed->reference,
                    $name === $resolveImportedName ? null : $resolveImportedName,
                );

                $relativePath = $this->resolveRelativePathAction->execute(
                    $location->segments,
                    $referencedTransformed->location,
                );

                $collection->add($relativePath, $importName);
            }
        }

        return $collection;
    }

    protected function resolveImportedName(
        array $usedNamesInScope,
        string $name,
    ): string {
        if ($this->alternativeNamesResolver) {
            return ($this->alternativeNamesResolver)($usedNamesInScope, $name);
        }

        if (! in_array($name, $usedNamesInScope)) {
            return $name;
        }

        if (! in_array("{$name}Import", $usedNamesInScope)) {
            return "{$name}Import";
        }

        $counter = 2;

        while (in_array("{$name}Import{$counter}", $usedNamesInScope)) {
            $counter++;
        }

        return "{$name}Import{$counter}";
    }
}
