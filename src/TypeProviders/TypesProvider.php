<?php

namespace Spatie\TypeScriptTransformer\TypeProviders;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

interface TypesProvider
{
    public function provide(
        TypeScriptTransformerConfig $config,
        TransformedCollection $types
    ): void;
}
