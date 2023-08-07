<?php

namespace Spatie\TypeScriptTransformer\TypeProviders;

use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

interface TypesProvider
{
    public function provide(
        TypeScriptTransformerConfig $config,
        TransformedCollection $types
    ): void;
}
