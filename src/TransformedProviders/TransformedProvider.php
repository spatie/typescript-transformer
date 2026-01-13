<?php

namespace Spatie\TypeScriptTransformer\TransformedProviders;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

interface TransformedProvider
{
    public function provide(
        TypeScriptTransformerConfig $config,
        TransformedCollection $types
    ): void;
}
