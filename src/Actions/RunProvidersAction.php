<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Collections\WritersCollection;
use Spatie\TypeScriptTransformer\Support\Loggers\Logger;
use Spatie\TypeScriptTransformer\TransformedProviders\LoggingTransformedProvider;
use Spatie\TypeScriptTransformer\TransformedProviders\StandaloneWritingTransformedProvider;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class RunProvidersAction
{
    public function __construct(
        protected TypeScriptTransformerConfig $config,
    ) {
    }

    /**
     * @return array{TransformedCollection, WritersCollection}
     */
    public function execute(
        Logger $logger,
    ): array {
        $transformedCollection = new TransformedCollection();
        $writersCollection = new WritersCollection($this->config->typesWriter);

        foreach ($this->config->transformedProviders as $transformedProvider) {
            if ($transformedProvider instanceof LoggingTransformedProvider) {
                $transformedProvider->setLogger($logger);
            }

            $writer = $this->config->typesWriter;

            if ($transformedProvider instanceof StandaloneWritingTransformedProvider) {
                $writer = $transformedProvider->getWriter();

                $writersCollection->addStandaloneWriter($writer);
            }

            $transformed = $transformedProvider->provide($this->config);

            foreach ($transformed as $item) {
                $item->setWriter($writer);
            }

            $transformedCollection->add(...$transformed);
        }

        return [$transformedCollection, $writersCollection];
    }
}
