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
        foreach ($this->config->defaultTypeProviders as $defaultTypeProviderClass) {
            /** @var DefaultTypesProvider $defaultTypeProvider */
            $defaultTypeProvider = new $defaultTypeProviderClass;

            $collection->add(...$defaultTypeProvider->provide());
        }
    }
}
