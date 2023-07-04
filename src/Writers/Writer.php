<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Collections\ReferenceMap;
use Spatie\TypeScriptTransformer\Support\WrittenFile;

interface Writer
{
    /** @return array<WrittenFile> */
    public function output(
        array $transformedTypes,
        ReferenceMap $referenceMap,
    ): array;
}
