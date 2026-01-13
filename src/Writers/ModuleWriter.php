<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Actions\CleanupReferencesAction;
use Spatie\TypeScriptTransformer\Actions\SplitTransformedPerLocationAction;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Data\GlobalNamespaceReferenced;
use Spatie\TypeScriptTransformer\Data\ImportedReferenced;
use Spatie\TypeScriptTransformer\Support\Location;
use Spatie\TypeScriptTransformer\Support\WriteableFile;
use Spatie\TypeScriptTransformer\Support\WritingContext;
use Spatie\TypeScriptTransformer\Transformed\Transformed;

class ModuleWriter implements Writer
{
    public function __construct(
        protected string $moduleFilename = 'index.ts',
        protected SplitTransformedPerLocationAction $transformedPerLocationAction = new SplitTransformedPerLocationAction(),
        protected CleanupReferencesAction $cleanupReferencesAction = new CleanupReferencesAction(),
    ) {
    }

    public function output(
        array $transformed,
        TransformedCollection $collection,
    ): array {
        $locations = $this->transformedPerLocationAction->execute(
            $transformed
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
        $filePath = $this->resolveRelativePath($location->segments);

        [$imports, $nameMap] = $this->cleanupReferencesAction->execute(
            $this,
            $filePath,
            $location->transformed,
            $collection
        );

        $output = '';

        $writingContext = new WritingContext($nameMap);

        foreach ($imports->getTypeScriptNodes() as $import) {
            $output .= $import->write($writingContext).PHP_EOL;
        }

        foreach ($location->transformed as $transformedItem) {
            $output .= $transformedItem->write($writingContext).PHP_EOL;
        }

        return new WriteableFile($filePath, $output);
    }

    /** @param  array<string> $location */
    protected function resolveRelativePath(
        array $location,
    ): string {
        if (count($location) === 0) {
            return $this->moduleFilename;
        }

        return implode(DIRECTORY_SEPARATOR, $location).DIRECTORY_SEPARATOR.$this->moduleFilename;
    }
    public function resolveReferenced(Transformed $transformed): ImportedReferenced|GlobalNamespaceReferenced
    {
        return new ImportedReferenced(
            $transformed->getName(),
            $this->resolveRelativePath($transformed->location)
        );
    }
}
