<?php

namespace Spatie\TypeScriptTransformer\TransformedProviders;

use Spatie\TypeScriptTransformer\Transformed\Transformed;

interface TransformedProvider
{
    /**
     * @return array<Transformed>
     */
    public function provide(): array;
}
