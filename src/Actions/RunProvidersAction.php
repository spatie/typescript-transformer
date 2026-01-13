<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Support\Console\Logger;
use Spatie\TypeScriptTransformer\TransformedProviders\LoggingTransformedProvider;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class RunProvidersAction
{
    public function __construct(
        protected TypeScriptTransformerConfig $config,
    ) {
    }

    public function execute(
        Logger $logger,
    ): TransformedCollection {
        $collection = new TransformedCollection();

        foreach ($this->config->transformedProviders as $transformedProvider) {
            if ($transformedProvider instanceof LoggingTransformedProvider) {
                $transformedProvider->setLogger($logger);
            }

            $transformedProvider->provide(
                $this->config,
                $collection
            );
        }

        return $collection;
    }
}
