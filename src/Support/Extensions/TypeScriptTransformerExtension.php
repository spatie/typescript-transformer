<?php

namespace Spatie\TypeScriptTransformer\Support\Extensions;

use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;

interface TypeScriptTransformerExtension
{
    public function enrich(TypeScriptTransformerConfigFactory $factory): void;
}
