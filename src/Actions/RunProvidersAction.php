<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Collections\PhpNodeCollection;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Support\Loggers\Logger;
use Spatie\TypeScriptTransformer\TransformedProviders\ActionAwareTransformedProvider;
use Spatie\TypeScriptTransformer\TransformedProviders\ConfigAwareTransformedProvider;
use Spatie\TypeScriptTransformer\TransformedProviders\LoggingTransformedProvider;
use Spatie\TypeScriptTransformer\TransformedProviders\PhpNodesAwareTransformedProvider;
use Spatie\TypeScriptTransformer\TransformedProviders\TransformedProviderActions;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class RunProvidersAction
{
    public function __construct(
        protected TypeScriptTransformerConfig $config,
    ) {
    }

    public function execute(
        Logger $logger,
        PhpNodeCollection $phpNodeCollection,
        TransformedProviderActions $actions,
    ): TransformedCollection {
        $transformedCollection = new TransformedCollection();

        foreach ($this->config->transformedProviders as $transformedProvider) {
            if ($transformedProvider instanceof LoggingTransformedProvider) {
                $transformedProvider->setLogger($logger);
            }

            if ($transformedProvider instanceof PhpNodesAwareTransformedProvider) {
                $transformedProvider->setPhpNodeCollection($phpNodeCollection);
            }

            if ($transformedProvider instanceof ConfigAwareTransformedProvider) {
                $transformedProvider->setConfig($this->config);
            }

            if ($transformedProvider instanceof ActionAwareTransformedProvider) {
                $transformedProvider->setActions($actions);
            }

            $transformedCollection->add(...$transformedProvider->provide());
        }

        return $transformedCollection;
    }
}
