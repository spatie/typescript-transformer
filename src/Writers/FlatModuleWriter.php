<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Actions\ResolveImportsAndResolvedReferenceMapAction;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Data\GlobalNamespaceResolvedReference;
use Spatie\TypeScriptTransformer\Data\ModuleImportResolvedReference;
use Spatie\TypeScriptTransformer\Support\WriteableFile;
use Spatie\TypeScriptTransformer\Support\WritingContext;
use Spatie\TypeScriptTransformer\Transformed\Transformed;

class FlatModuleWriter implements Writer
{
    protected ResolveImportsAndResolvedReferenceMapAction $resolveImportsAndResolvedReferenceMapAction;

    public function __construct(
        public string $path = 'types.ts',
    ) {
        $this->resolveImportsAndResolvedReferenceMapAction = new ResolveImportsAndResolvedReferenceMapAction();
    }

    public function output(
        array $transformed,
        TransformedCollection $transformedCollection,
    ): array {
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

        foreach ($transformed as $item) {
            $output .= $item->write($writingContext).PHP_EOL;
        }

        return [new WriteableFile($this->path, $output)];
    }

    public function resolveReference(Transformed $transformed): ModuleImportResolvedReference|GlobalNamespaceResolvedReference
    {
        return new ModuleImportResolvedReference(
            $transformed->getName(),
            $this->path
        );
    }
}
