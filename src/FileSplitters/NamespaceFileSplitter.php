<?php

namespace Spatie\TypeScriptTransformer\FileSplitters;

use Spatie\TypeScriptTransformer\Actions\ResolveRelativePathAction;
use Spatie\TypeScriptTransformer\Structures\SplitTypesCollection;
use Spatie\TypeScriptTransformer\Structures\OldTransformedType;
use Spatie\TypeScriptTransformer\Structures\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Structures\TypeImport;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use function PHPUnit\Framework\isEmpty;

class NamespaceFileSplitter implements FileSplitter
{
    protected string $extension;

    protected string $filename;

    protected ResolveRelativePathAction $resolveRelativePathAction;

    public function __construct(
        array $options
    ) {
        $this->extension = $options['extension'] ?? 'd.ts';
        $this->filename = $options['filename'] ?? 'types';

        $this->resolveRelativePathAction = new ResolveRelativePathAction();
    }

    public function split(string $outputPath, TypesCollection $typesCollection): array
    {
        /** @var \Spatie\TypeScriptTransformer\Structures\SplitTypesCollection[] $splits */
        $splits = [];

        foreach ($typesCollection as $type) {
            $namespaceSegments = $type->name->namespaceSegments;

            $partialPath = implode('/', $namespaceSegments);

            $path = "{$outputPath}/{$partialPath}/{$this->filename}.{$this->extension}";

            if (! array_key_exists($path, $splits)) {
                $splits[$path] = new SplitTypesCollection(
                    $path,
                    TypesCollection::create(),
                    []
                );
            }

            $splits[$path]->types->add($type);
            $splits[$path]->addImport(...$this->typeToImports($namespaceSegments, $type));
        }

        foreach ($splits as $split) {
            $split->cleanupImports();
        }

        return array_values($splits);
    }

    protected function typeToImports(
        array $currentNamespaceSegments,
        Transformed $transformed
    ): array {
        $imports = [];

        foreach ($transformed->typeReferences as $typeReference) {
            if ($typeReference->referenced === null) {
                continue; // Type was not transformed to TypeScript
            }

            $path = $this->resolveRelativePathAction->execute($currentNamespaceSegments, $typeReference->namespaceSegments);

            if ($path === null) {
                continue; // Same file
            }

            $imports[] = new TypeImport(
                "{$path}/{$this->filename}",
                [$typeReference->name]
            );
        }

        return $imports;
    }

}
