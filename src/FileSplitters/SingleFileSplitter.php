<?php

namespace Spatie\TypeScriptTransformer\FileSplitters;

use Spatie\TypeScriptTransformer\Structures\SplitTypesCollection;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;

/**
 * @property array{filename: string} $options
 */
class SingleFileSplitter implements FileSplitter
{
    protected string $filename;

    public function __construct(array $options)
    {
        $this->filename = ltrim($options['filename'] ?? 'types.d.ts', '/');
    }

    public function split(string $outputPath, TypesCollection $typesCollection): array
    {
        return [
            new SplitTypesCollection(
                "{$outputPath}/{$this->filename}",
                $typesCollection,
                []
            ),
        ];
    }
}
