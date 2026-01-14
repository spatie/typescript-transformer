<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Data\GlobalNamespaceResolvedReference;
use Spatie\TypeScriptTransformer\Data\ModuleImportResolvedReference;
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
        TransformedCollection $transformedCollection,
    ): array;

    public function resolveReference(Transformed $transformed): ModuleImportResolvedReference|GlobalNamespaceResolvedReference;
}
