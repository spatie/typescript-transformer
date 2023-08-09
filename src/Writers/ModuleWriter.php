<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Actions\ResolveModuleImportsAction;
use Spatie\TypeScriptTransformer\Actions\SplitTransformedPerLocationAction;
use Spatie\TypeScriptTransformer\Collections\ReferenceMap;
use Spatie\TypeScriptTransformer\References\Reference;
use Spatie\TypeScriptTransformer\Support\Location;
use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\Support\WriteableFile;
use Spatie\TypeScriptTransformer\Support\WritingContext;

class ModuleWriter implements Writer
{
    public function __construct(
        protected string $path,
        protected SplitTransformedPerLocationAction $transformedPerLocationAction = new SplitTransformedPerLocationAction(),
        protected ResolveModuleImportsAction $resolveModuleImportsAction = new ResolveModuleImportsAction(),
    ) {
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
    ): WriteableFile {
        $imports = $this->resolveModuleImportsAction->execute($location);

        $output = '';

        $writingContext = new WritingContext(function (Reference $reference) use ($location, $referenceMap, $imports) {
            if ($name = $imports->getAliasOrNameForReference($reference)) {
                return $name;
            }

            // Type declared somewhere else in the module
            return $referenceMap->get($reference)->getName();
        });

        foreach ($imports->getTypeScriptNodes() as $import) {
            $output .= $import->write($writingContext);
        }

        if ($imports->isEmpty() === false) {
            $output .= PHP_EOL;
        }

        foreach ($location->transformed as $transformedItem) {
            $output .= $transformedItem->prepareForWrite()->write($writingContext);
        }

        return new WriteableFile("{$this->resolvePath($location)}/index.ts", $output);
    }

    protected function resolvePath(
        Location $location,
    ): string {
        $basePath = rtrim($this->path, '/');

        if (count($location->segments) === 0) {
            return $basePath;
        }

        return $basePath.'/'.implode('/', $location->segments);
    }
}
