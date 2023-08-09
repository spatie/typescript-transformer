<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Collections\ReferenceMap;
use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;
use Spatie\TypeScriptTransformer\Support\WriteableFile;

interface Writer
{
    /** @return array<WriteableFile> */
    public function output(
        TransformedCollection $collection,
        ReferenceMap $referenceMap,
    ): array;
}
