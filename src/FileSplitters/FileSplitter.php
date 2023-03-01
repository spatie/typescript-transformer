<?php

namespace Spatie\TypeScriptTransformer\FileSplitters;

use Spatie\TypeScriptTransformer\Structures\TypesCollection;

interface FileSplitter
{
    public function __construct(array $options);

    /** @return \Spatie\TypeScriptTransformer\Structures\SplitTypesCollection[] */
    public function split(
        string $outputPath,
        TypesCollection $typesCollection,
    ): array;
}
