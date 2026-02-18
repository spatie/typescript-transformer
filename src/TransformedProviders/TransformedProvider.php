<?php

namespace Spatie\TypeScriptTransformer\TransformedProviders;

use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

interface TransformedProvider
{
    /**
     * @return array<Transformed>
     */
    public function provide(
        TypeScriptTransformerConfig $config,
    ): array;
}
