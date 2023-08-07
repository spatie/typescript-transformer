<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Actions\ResolveRelativePathAction;
use Spatie\TypeScriptTransformer\Actions\SplitTransformedPerLocationAction;
use Spatie\TypeScriptTransformer\Collections\ReferenceMap;
use Spatie\TypeScriptTransformer\References\Reference;
use Spatie\TypeScriptTransformer\Support\ImportName;
use Spatie\TypeScriptTransformer\Support\Location;
use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\Support\WritingContext;
use Spatie\TypeScriptTransformer\Support\WrittenFile;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptImport;

class ModuleWriter implements Writer
{
    protected string $path;

    protected SplitTransformedPerLocationAction $transformedPerLocationAction;

    protected ResolveRelativePathAction $resolveRelativePathAction;

    public function __construct(
        string $path,
        protected string $filename = 'types',
        protected string $extension = 'ts',
    ) {
        $this->path = rtrim($path, '/');
        $this->transformedPerLocationAction = new SplitTransformedPerLocationAction();
        $this->resolveRelativePathAction = new ResolveRelativePathAction();
    }

    public function output(TransformedCollection $collection, ReferenceMap $referenceMap): array
    {
        $locations = $this->transformedPerLocationAction->execute(
            $collection
        );

        $writtenFiles = [];

        foreach ($locations as $location) {
            $writtenFiles[] = $this->writeLocation($location, $referenceMap);
        }

        return $writtenFiles;
    }

    protected function writeLocation(
        Location $location,
        ReferenceMap $referenceMap,
    ): WrittenFile {
        $imports = $this->resolveImports($location, $referenceMap);

        $path = "{$this->path}/".implode('/', $location->segments).'/';

        $output = '';

        $writingContext = new WritingContext(function (Reference $reference) use ($referenceMap) {
            return $referenceMap->get($reference)->getName();
        });

        foreach ($imports as $import) {
            $output .= $import->write($writingContext);
        }

        $output .= PHP_EOL;

        foreach ($location->transformed as $transformedItem) {
            $output .= $transformedItem->prepareForWrite()->write($writingContext);
        }

        if (is_dir($path) === false) {
            mkdir($path, recursive: true);
        }

        file_put_contents("{$path}/{$this->filename}.{$this->extension}", $output);

        return new WrittenFile($path);
    }

    /**
     * @return array<TypeScriptImport>
     */
    protected function resolveImports(
        Location $location,
        ReferenceMap $referenceMap,
    ): array {
        /** @var array<string, array{location: array<string>, names: array<ImportName>}> $imports */
        $imports = [];

        // TODO: right now, we import directly the name
        // Take a look at the LengthAwarePaginator interface, it imports LengthAwarePaginator which does not work
        //

        $usedNamesInScope = array_values(
            array_map(fn (Transformed $transformed) => $transformed->getName(), $location->transformed)
        );

        foreach ($location->transformed as $transformedItem) {
            foreach ($transformedItem->references as $reference) {
                $transformedReference = $referenceMap->get($reference);

                if (! array_key_exists(implode($transformedReference->location), $imports)) {
                    $imports[implode($transformedReference->location)] = [
                        'location' => $transformedReference->location,
                        'names' => [],
                    ];
                }

                $resolveImportedName = $this->resolveImportedName($usedNamesInScope, $transformedReference->getName());

                $usedNamesInScope[] = $resolveImportedName;

                $imports[implode($transformedReference->location)]['names'][] = ImportName::fromNameAndImportedName(
                    $transformedReference->getName(),
                    $resolveImportedName,
                );

                // TODO: the reference should now point to the alias if used
                // This is not possible at the moment because we don't have any idea about the node
                // Ideally the references list is actually a list with pointers to the nodes
            }
        }

        return array_filter(array_map(function (array $import) use ($location) {
            $names = array_values($import['names']);

            $path = $this->resolveRelativePathAction->execute(
                $location->segments,
                $import['location'],
            );

            if ($path === null) {
                // current path
                return null;
            }

            return new TypeScriptImport(
                "{$path}/{$this->filename}",
                $names
            );
        }, $imports));
    }

    protected function resolveImportedName(
        array $usedNamesInScope,
        string $name,
    ): string {
        if (! in_array($name, $usedNamesInScope)) {
            return $name;
        }

        if (! in_array("{$name}Alt", $usedNamesInScope)) {
            return "{$name}Alt";
        }

        $counter = 2;

        while (in_array("{$name}Alt{$counter}", $usedNamesInScope)) {
            $counter++;
        }

        return "{$name}Alt{$counter}";
    }
}
