<?php

namespace Spatie\TypeScriptTransformer\FileSplitters;

use Spatie\TypeScriptTransformer\Structures\SplitTypesCollection;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;

class NamespaceFileSplitter implements FileSplitter
{
    protected string $extension;

    protected string $filename;

    protected bool $splitPerType;

    public function __construct(array $options)
    {
        $this->extension = $options['extension'] ?? 'd.ts';
        $this->filename = $options['filename'] ?? 'types';
        $this->splitPerType = $options['splitPerType'] ?? false;
    }

    public function split(string $outputPath, TypesCollection $typesCollection): array
    {
        /** @var \Spatie\TypeScriptTransformer\Structures\SplitTypesCollection[] $splits */
        $splits = [];

        foreach ($typesCollection as $type) {
            $partialPath = implode('/', $type->getNamespaceSegments());

            $path = "{$outputPath}/{$partialPath}/{$this->filename}.{$this->extension}";

            if (! array_key_exists($path, $splits)) {
                $splits[$path] = new SplitTypesCollection(
                    $path,
                    TypesCollection::create(),
                    []
                );
            }

            $splits[$path]->types->add($type);
            $splits[$path]->addImport(...$this->typeToImports($type));
        }

        foreach ($splits as $split) {
            $split->cleanupImports();
        }

        return array_values($splits);
    }

    protected function typeToImports(
        TransformedType $type
    ): array {
        ray($type->typeReferences);

        return [];
    }
}
