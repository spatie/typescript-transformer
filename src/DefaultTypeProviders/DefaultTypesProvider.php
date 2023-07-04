<?php

namespace Spatie\TypeScriptTransformer\DefaultTypeProviders;

use Spatie\TypeScriptTransformer\Transformed\Transformed;

interface DefaultTypesProvider
{
    /** @return array<Transformed> */
    public function provide(): array;
}
