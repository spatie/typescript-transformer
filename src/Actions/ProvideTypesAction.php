<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;
use Spatie\TypeScriptTransformer\TypeProviders\TypesProvider;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class ProvideTypesAction
{
    public function __construct(
        protected TypeScriptTransformerConfig $config,
        public TypeScriptTransformerLog $log,
    ) {
    }

    public function execute(TransformedCollection $collection): void
    {
        foreach ($this->config->typeProviders as $defaultTypeProvider) {
            $defaultTypeProvider = $defaultTypeProvider instanceof TypesProvider
                ? $defaultTypeProvider
                : new $defaultTypeProvider;

            $defaultTypeProvider->provide(
                $this->config,
                $this->log,
                $collection
            );
        }
    }
}
