<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Collections\ReferenceMap;
use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;
use Spatie\TypeScriptTransformer\Support\WrittenFile;

interface Writer
{
    /** @return array<WrittenFile> */
    public function output(
        TransformedCollection $collection,
        ReferenceMap $referenceMap,
    ): array;
}
