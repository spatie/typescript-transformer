<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Support\WriteableFile;

interface Writer
{
    /** @return array<WriteableFile> */
    public function output(
        TransformedCollection $collection,
    ): array;
}
