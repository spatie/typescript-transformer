<?php

namespace Spatie\TypeScriptTransformer\TransformedProviders;

use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

interface ConfigAwareTransformedProvider
{
    public function setConfig(TypeScriptTransformerConfig $config): void;
}
