<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Actions\ResolveImportsAndResolvedReferenceMapAction;
use Spatie\TypeScriptTransformer\Actions\SplitTransformedPerLocationAction;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Data\GlobalNamespaceResolvedReference;
use Spatie\TypeScriptTransformer\Data\Location;
use Spatie\TypeScriptTransformer\Data\ModuleImportResolvedReference;
use Spatie\TypeScriptTransformer\Data\WriteableFile;
use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\Transformed\Transformed;

class ModuleWriter implements Writer
{
    public function __construct(
        protected ?string $path = 'types',
        protected string $moduleFilename = 'index.ts',
        protected SplitTransformedPerLocationAction $transformedPerLocationAction = new SplitTransformedPerLocationAction(),
        protected ResolveImportsAndResolvedReferenceMapAction $cleanupReferencesAction = new ResolveImportsAndResolvedReferenceMapAction(),
    ) {
    }

    public function output(
        array $transformed,
        TransformedCollection $transformedCollection,
    ): array {
        $locations = $this->transformedPerLocationAction->execute(
            $transformed
        );

        $writtenFiles = [];

        foreach ($locations as $location) {
            $writtenFiles[] = $this->writeLocation($location, $transformedCollection);
        }

        return $writtenFiles;
    }

    protected function writeLocation(
        Location $location,
        TransformedCollection $transformedCollection,
    ): WriteableFile {
        $filePath = $this->resolveRelativePath($location->segments);

        [$imports, $resolvedReferenceMap] = $this->cleanupReferencesAction->execute(
            $filePath,
            $location->transformed,
            $transformedCollection
        );

        $output = '';

        $writingContext = new WritingContext($resolvedReferenceMap);

        foreach ($imports->getTypeScriptNodes() as $import) {
            $output .= $import->write($writingContext).PHP_EOL;
        }

        foreach ($location->transformed as $transformedItem) {
            $output .= $transformedItem->write($writingContext).PHP_EOL;
        }

        return new WriteableFile($filePath, $output);
    }

    /** @param array<string> $location */
    protected function resolveRelativePath(array $location): string
    {
        $segments = $this->path !== null
            ? [$this->path, ...$location]
            : $location;

        if (count($segments) === 0) {
            return $this->moduleFilename;
        }

        return implode(DIRECTORY_SEPARATOR, $segments).DIRECTORY_SEPARATOR.$this->moduleFilename;
    }
    public function resolveReference(Transformed $transformed): ModuleImportResolvedReference|GlobalNamespaceResolvedReference
    {
        return new ModuleImportResolvedReference(
            $transformed->getName(),
            $this->resolveRelativePath($transformed->location)
        );
    }
}
