<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Collections\ImportsCollection;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Data\GlobalNamespaceReferenced;
use Spatie\TypeScriptTransformer\Data\ImportedReferenced;
use Spatie\TypeScriptTransformer\Support\ImportName;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Writers\Writer;

class CleanupReferencesAction
{
    // TODO: clean this up so it is readable

    /**
     * @param Writer $currentWriter The writer that's outputting the current file
     * @param string $currentFilePath Path to the file being written (relative to output directory)
     * @param array<Transformed> $transformedItems Items being written to this file
     * @param TransformedCollection $collection All transformed items
     *
     * @return array{ImportsCollection, array<string, string>} Imports and name map (reference key => resolved name)
     */
    public function execute(
        Writer $currentWriter,
        string $currentFilePath,
        array $transformedItems,
        TransformedCollection $collection,
    ): array {
        $importsCollection = new ImportsCollection();
        $nameMap = [];

        // Collect all names used in the current file to detect conflicts
        $usedNames = array_map(fn (Transformed $t) => $t->getName(), $transformedItems);

        foreach ($transformedItems as $transformedItem) {
            foreach ($transformedItem->references as $referenceKey => $typeReferences) {
                // Skip if already processed
                if (isset($nameMap[$referenceKey])) {
                    continue;
                }

                $referencedTransformed = $collection->get($referenceKey);

                // If writer is not set yet, use the current writer as fallback
                try {
                    $writer = $referencedTransformed->getWriter();
                } catch (\RuntimeException) {
                    $writer = $currentWriter;
                }

                $resolvedReference = $writer->resolveReferenced($referencedTransformed);

                if ($resolvedReference instanceof GlobalNamespaceReferenced) {
                    // For namespaced references, just map to the qualified name
                    $nameMap[$referenceKey] = $resolvedReference->qualifiedName;
                } elseif ($resolvedReference instanceof ImportedReferenced) {
                    // Check if it's the same file - no import needed
                    if ($resolvedReference->outputPath === $currentFilePath) {
                        $nameMap[$referenceKey] = $resolvedReference->name;

                        continue;
                    }

                    // Calculate relative path from current file to target file
                    $relativePath = $this->calculateRelativePath($currentFilePath, $resolvedReference->outputPath);

                    // Resolve name conflicts
                    $resolvedName = $this->resolveNameConflict($usedNames, $resolvedReference->name);
                    $usedNames[] = $resolvedName;

                    // Create import name with alias if needed
                    $importName = new ImportName(
                        $resolvedReference->name,
                        $referencedTransformed->reference,
                        $resolvedReference->name === $resolvedName ? null : $resolvedName
                    );

                    $importsCollection->add($relativePath, $importName);
                    $nameMap[$referenceKey] = $resolvedName;
                }
            }
        }

        return [$importsCollection, $nameMap];
    }

    protected function calculateRelativePath(string $fromPath, string $toPath): string
    {
        $from = dirname($fromPath);
        $to = dirname($toPath);

        $fromParts = $from === '.' ? [] : explode(DIRECTORY_SEPARATOR, $from);
        $toParts = $to === '.' ? [] : explode(DIRECTORY_SEPARATOR, $to);

        // Find common ancestor
        $commonLength = 0;
        $minLength = min(count($fromParts), count($toParts));

        for ($i = 0; $i < $minLength; $i++) {
            if ($fromParts[$i] === $toParts[$i]) {
                $commonLength++;
            } else {
                break;
            }
        }

        // Build relative path
        $upLevels = count($fromParts) - $commonLength;
        $relativeParts = array_fill(0, $upLevels, '..');
        $remainingParts = array_slice($toParts, $commonLength);
        $allParts = array_merge($relativeParts, $remainingParts);

        $relativePath = count($allParts) > 0 ? implode('/', $allParts) : '.';

        // Get the filename without extension
        $toFilename = pathinfo($toPath, PATHINFO_FILENAME);

        // If the filename is 'index', omit it from the import path (TypeScript convention)
        if ($toFilename === 'index') {
            $importPath = $relativePath;
        } else {
            // Combine directory path with filename
            $importPath = $relativePath === '.' ? "./{$toFilename}" : "{$relativePath}/{$toFilename}";
        }

        // Add ./ or ../ prefixes as needed
        // Rules:
        // - Simple names like 'nested' or 'nested/foo' stay as is (no ./ prefix)
        // - Parent paths like '..' need trailing slash '../'
        // - Paths starting with ../ are kept as is
        if ($importPath === '.') {
            // Current directory
            $importPath = '.';
        } elseif ($importPath === '..') {
            // Parent directory only, add trailing slash
            $importPath = '../';
        } elseif (str_starts_with($importPath, '../') || str_starts_with($importPath, './')) {
            // Already has a relative prefix, keep as is
        } else {
            // Everything else (nested, nested/foo, etc.) stays as is
        }

        return $importPath;
    }

    protected function resolveNameConflict(array $usedNames, string $name): string
    {
        if (! in_array($name, $usedNames)) {
            return $name;
        }

        if (! in_array("{$name}Import", $usedNames)) {
            return "{$name}Import";
        }

        $counter = 2;

        while (in_array("{$name}Import{$counter}", $usedNames)) {
            $counter++;
        }

        return "{$name}Import{$counter}";
    }
}
