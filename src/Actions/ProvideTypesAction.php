<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Support\Console\Logger;
use Spatie\TypeScriptTransformer\TypeProviders\LoggingTypesProvider;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class ProvideTypesAction
{
    public function __construct(
        protected TypeScriptTransformerConfig $config,
    ) {
    }

    public function execute(
        Logger $logger,
    ): TransformedCollection {
        $collection = new TransformedCollection();

        foreach ($this->config->typeProviders as $typeProvider) {
            if ($typeProvider instanceof LoggingTypesProvider) {
                $typeProvider->setLogger($logger);
            }

            $typeProvider->provide(
                $this->config,
                $collection
            );
        }

        return $collection;
    }
}
