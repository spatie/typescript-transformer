<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Actions\ResolveModuleImportsAction;
use Spatie\TypeScriptTransformer\Actions\SplitTransformedPerLocationAction;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\References\Reference;
use Spatie\TypeScriptTransformer\Support\Location;
use Spatie\TypeScriptTransformer\Support\WriteableFile;
use Spatie\TypeScriptTransformer\Support\WritingContext;

class ModuleWriter implements Writer, MultipleFilesWriter
{
    public function __construct(
        protected string $moduleFilename = 'index.ts',
        protected SplitTransformedPerLocationAction $transformedPerLocationAction = new SplitTransformedPerLocationAction(),
        protected ResolveModuleImportsAction $resolveModuleImportsAction = new ResolveModuleImportsAction(),
    ) {
    }

    public function output(
        TransformedCollection $collection,
    ): array {
        $locations = $this->transformedPerLocationAction->execute(
            $collection
        );

        $writtenFiles = [];

        foreach ($locations as $location) {
            $writtenFiles[] = $this->writeLocation($location, $collection);
        }

        return $writtenFiles;
    }

    protected function writeLocation(
        Location $location,
        TransformedCollection $collection,
    ): WriteableFile {
        $imports = $this->resolveModuleImportsAction->execute($location, $collection);

        $output = '';

        $writingContext = new WritingContext(function (Reference $reference) use ($collection, $imports) {
            if ($name = $imports->getAliasOrNameForReference($reference)) {
                return $name;
            }

            // Type declared somewhere else in the module
            return $collection->get($reference)->getName();
        });

        foreach ($imports->getTypeScriptNodes() as $import) {
            $output .= $import->write($writingContext).PHP_EOL;
        }

        if ($imports->isEmpty() === false) {
            $output .= PHP_EOL;
        }

        foreach ($location->transformed as $transformedItem) {
            $output .= $transformedItem->write($writingContext).PHP_EOL;
        }

        return new WriteableFile($this->resolveRelativePath($location), $output);
    }

    protected function resolveRelativePath(
        Location $location,
    ): string {
        if (count($location->segments) === 0) {
            return $this->moduleFilename;
        }

        return implode(DIRECTORY_SEPARATOR, $location->segments).DIRECTORY_SEPARATOR.$this->moduleFilename;
    }
}
