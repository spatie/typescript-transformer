<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Actions\ResolveRelativePathAction;
use Spatie\TypeScriptTransformer\Actions\SplitTransformedPerLocationAction;
use Spatie\TypeScriptTransformer\Collections\ReferenceMap;
use Spatie\TypeScriptTransformer\References\Reference;
use Spatie\TypeScriptTransformer\Support\Location;
use Spatie\TypeScriptTransformer\Support\WritingContext;
use Spatie\TypeScriptTransformer\Support\WrittenFile;
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

    public function output(array $transformedTypes, ReferenceMap $referenceMap): array
    {
        $locations = $this->transformedPerLocationAction->execute(
            $transformedTypes
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
            return $referenceMap->get($reference)->name;
        });

        foreach ($imports as $import) {
            $output .= $import->write($writingContext);
        }

        $output .= PHP_EOL;

        foreach ($location->transformed as $transformedItem) {
            $output .= $transformedItem->typeScriptNode->write($writingContext);
        }

        if (is_dir($path) === false) {
            mkdir($path, recursive: true);
        }

        file_put_contents("{$path}/{$this->filename}.{$this->extension}", $output);

        return new WrittenFile($path, $location->transformed);
    }

    /**
     * @return array<TypeScriptImport>
     */
    protected function resolveImports(
        Location $location,
        ReferenceMap $referenceMap,
    ): array {
        /** @var array<string, array{location: array<string>, names: array<string>}> $imports */
        $imports = [];

        foreach ($location->transformed as $transformedItem) {
            foreach ($transformedItem->references as $reference) {
                $transformedReference = $referenceMap->get($reference);

                if (! array_key_exists(implode($transformedReference->location), $imports)) {
                    $imports[implode($transformedReference->location)] = [
                        'location' => $transformedReference->location,
                        'names' => [],
                    ];
                }

                $imports[implode($transformedReference->location)]['names'][] = $transformedReference->name;
            }
        }

        return array_filter(array_map(function (array $import) use ($location) {
            $names = array_values(array_unique($import['names']));
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
}
