<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Data\GlobalNamespaceReferenced;
use Spatie\TypeScriptTransformer\Data\ImportedReferenced;
use Spatie\TypeScriptTransformer\Support\WriteableFile;
use Spatie\TypeScriptTransformer\Transformed\Transformed;

interface Writer
{
    /**
     * @param array<Transformed> $transformed
     *
     * @return array<WriteableFile>
     */
    public function output(
        array $transformed,
        TransformedCollection $collection,
    ): array;

    public function resolveReferenced(Transformed $transformed): ImportedReferenced|GlobalNamespaceReferenced;
}
