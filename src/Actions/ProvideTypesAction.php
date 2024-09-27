<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\TypeProviders\TypesProvider;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class ProvideTypesAction
{
    public function __construct(
        protected TypeScriptTransformerConfig $config,
    ) {
    }

    public function execute(): TransformedCollection
    {
        $collection = new TransformedCollection();

        foreach ($this->config->typeProviders as $typeProvider) {
            $typeProvider = $typeProvider instanceof TypesProvider
                ? $typeProvider
                : new $typeProvider();

            $typeProvider->provide(
                $this->config,
                $collection
            );
        }

        return $collection;
    }
}
