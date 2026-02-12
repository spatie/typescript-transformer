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
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNamespace;

class GlobalNamespaceWriter implements Writer
{
    public function __construct(
        protected string $path = 'types.d.ts',
        protected SplitTransformedPerLocationAction $splitTransformedPerLocationAction = new SplitTransformedPerLocationAction(),
        protected ResolveImportsAndResolvedReferenceMapAction $resolveImportsAndResolvedReferenceMapAction = new ResolveImportsAndResolvedReferenceMapAction(),
    ) {
        $this->path = $this->ensureDeclarationFileExtension($path);
    }

    protected function ensureDeclarationFileExtension(string $path): string
    {
        if (str_ends_with($path, '.d.ts')) {
            return $path;
        }

        $directory = pathinfo($path, PATHINFO_DIRNAME);
        $filename = pathinfo($path, PATHINFO_FILENAME);

        $baseName = $directory === '.'
            ? $filename
            : $directory.DIRECTORY_SEPARATOR.$filename;

        return "{$baseName}.d.ts";
    }

    public function output(
        array $transformed,
        TransformedCollection $transformedCollection,
    ): array {
        $root = $this->splitTransformedPerLocationAction->execute(
            $transformed
        );

        [$imports, $resolvedReferenceMap] = $this->resolveImportsAndResolvedReferenceMapAction->execute(
            $this->path,
            $transformed,
            $transformedCollection
        );

        $output = '';

        $writingContext = new WritingContext($resolvedReferenceMap);

        $hasImports = count($imports->getTypeScriptNodes()) > 0;

        foreach ($imports->getTypeScriptNodes() as $import) {
            $output .= $import->write($writingContext).PHP_EOL;
        }

        if ($hasImports) {
            $output .= 'declare global {'.PHP_EOL;
        }

        foreach ($root->transformed as $transformable) {
            $output .= $transformable->write($writingContext).PHP_EOL;
        }

        foreach ($root->children as $child) {
            $namespace = $this->buildNamespace($child, declare: ! $hasImports);

            $output .= $namespace->write($writingContext).PHP_EOL;
        }

        if ($hasImports) {
            $output .= '}'.PHP_EOL;
        }

        return [new WriteableFile($this->path, $output)];
    }

    protected function buildNamespace(Location $location, bool $declare): TypeScriptNamespace
    {
        $children = [];

        foreach ($location->children as $child) {
            $children[] = $this->buildNamespace($child, declare: false);
        }

        return new TypeScriptNamespace(
            $location->name,
            $location->transformed,
            $children,
            declare: $declare,
        );
    }

    public function resolveReference(Transformed $transformed): ModuleImportResolvedReference|GlobalNamespaceResolvedReference
    {
        $parts = [...$transformed->location, $transformed->getName()];

        return new GlobalNamespaceResolvedReference(implode('.', $parts));
    }
}
