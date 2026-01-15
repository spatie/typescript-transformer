<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Actions\ResolveImportsAndResolvedReferenceMapAction;
use Spatie\TypeScriptTransformer\Actions\SplitTransformedPerLocationAction;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Data\GlobalNamespaceResolvedReference;
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
        $split = $this->splitTransformedPerLocationAction->execute(
            $transformed
        );

        [$imports, $resolvedReferenceMap] = $this->resolveImportsAndResolvedReferenceMapAction->execute(
            $this->path,
            $transformed,
            $transformedCollection
        );

        $output = '';

        $writingContext = new WritingContext($resolvedReferenceMap);

        foreach ($imports->getTypeScriptNodes() as $import) {
            $output .= $import->write($writingContext).PHP_EOL;
        }

        foreach ($split as $splitConstruct) {
            if (count($splitConstruct->segments) === 0) {
                foreach ($splitConstruct->transformed as $transformable) {
                    $output .= $transformable->write($writingContext).PHP_EOL;
                }

                continue;
            }

            $namespace = new TypeScriptNamespace(
                $splitConstruct->segments,
                $splitConstruct->transformed
            );

            $output .= $namespace->write($writingContext).PHP_EOL;
        }

        return [new WriteableFile($this->path, $output)];
    }

    public function resolveReference(Transformed $transformed): ModuleImportResolvedReference|GlobalNamespaceResolvedReference
    {
        $parts = [...$transformed->location, $transformed->getName()];

        return new GlobalNamespaceResolvedReference(implode('.', $parts));
    }
}
