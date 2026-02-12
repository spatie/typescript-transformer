<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Closure;
use Spatie\TypeScriptTransformer\Collections\ModuleImportsCollection;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Data\GlobalNamespaceResolvedReference;
use Spatie\TypeScriptTransformer\Transformed\Transformed;

class ResolveImportsAndResolvedReferenceMapAction
{
    /**
     * @param Closure(string, array<string>):string|null $moduleImportNameResolver
     */
    public function __construct(
        protected ResolveRelativePathAction $resolveRelativePathAction = new ResolveRelativePathAction(),
        protected ?Closure $moduleImportNameResolver = null,
    ) {
    }

    /**
     * @param array<Transformed> $transformed
     * @param TransformedCollection $transformedCollection
     *
     * @return array{ModuleImportsCollection, array<string, string>}
     */
    public function execute(
        string $currentPath,
        array $transformed,
        TransformedCollection $transformedCollection,
    ): array {
        $importsCollection = new ModuleImportsCollection();

        /** @var array<string, string> $referenceMap */
        $referenceMap = []; // Reference key to resolved name

        $usedNamesWithinModule = array_map(
            fn (Transformed $transformedItem) => $transformedItem->getName(),
            $transformed
        );

        $additionalImports = [];

        foreach ($transformed as $transformedItem) {
            foreach ($transformedItem->references as $referenceKey => $typeReferences) {
                if (array_key_exists($referenceKey, $referenceMap)) {
                    continue;
                }

                $referenced = $transformedCollection->get($referenceKey);

                if ($referenced === null) {
                    continue;
                }

                $referencedWriter = $referenced->getWriter();

                $resolvedReference = $referencedWriter->resolveReference($referenced);

                if ($resolvedReference instanceof GlobalNamespaceResolvedReference) {
                    $referenceMap[$referenceKey] = $resolvedReference->qualifiedName;

                    continue;
                }

                if ($importsCollection->hasReferenceImported($referenced->reference)) {
                    continue;
                }

                if ($resolvedReference->path === $currentPath) {
                    $referenceMap[$referenceKey] = $resolvedReference->name;

                    continue;
                }

                if ($referenced->export === false) {
                    continue;
                }

                $relativePath = $this->resolveRelativePathAction->execute(
                    $currentPath,
                    $resolvedReference->path
                );

                $importsCollection->add($relativePath, $resolvedReference->name, $referenced->reference);
            }

            array_push($additionalImports, ...$transformedItem->additionalImports);
        }

        foreach ($additionalImports as $import) {
            foreach ($import->getReferenceKeys() as $name => $referenceKey) {
                if (array_key_exists($referenceKey, $referenceMap)) {
                    continue;
                }

                if ($importsCollection->hasReferenceImported($referenceKey)) {
                    continue;
                }

                $relativePath = $this->resolveRelativePathAction->execute(
                    $currentPath,
                    $import->path
                );

                if ($relativePath === null) {
                    $referenceMap[$referenceKey] = $name;

                    continue;
                }

                $importsCollection->add($relativePath, $name, $referenceKey);
            }
        }

        $importsCollection->aliasDuplicates(
            $usedNamesWithinModule,
            $this->moduleImportNameResolver,
        );

        $referenceMap = [...$referenceMap, ...$importsCollection->getReferenceMap()];

        return [$importsCollection, $referenceMap];
    }
}
