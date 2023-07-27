<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\DefaultTypeProviders\DefaultTypesProvider;
use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class AppendDefaultTypesAction
{
    public function __construct(
        protected TypeScriptTransformerConfig $config,
        public TypeScriptTransformerLog $log,
    ) {
    }

    public function execute(TransformedCollection $collection): void
    {
        foreach ($this->config->defaultTypeProviders as $defaultTypeProvider) {
            $defaultTypeProvider = $defaultTypeProvider instanceof DefaultTypesProvider
                ? $defaultTypeProvider
                : new $defaultTypeProvider;

            $collection->add(...$defaultTypeProvider->provide());
        }
    }
}
