<?php

namespace Spatie\TypeScriptTransformer\FileSplitters;

use Spatie\TypeScriptTransformer\Structures\SplitTypesCollection;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;

/**
 * @property array{filename: string} $options
 */
class SingleFileSplitter implements FileSplitter
{
    public function __construct(private array $options)
    {
    }

    public function split(string $outputPath, TypesCollection $typesCollection): array
    {
        return [
            new SplitTypesCollection(
                $outputPath . ltrim($this->options['filename'], '/'),
                $typesCollection,
                []
            ),
        ];
    }
}
