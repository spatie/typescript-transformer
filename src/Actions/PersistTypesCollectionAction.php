<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class PersistTypesCollectionAction
{
    protected TypeScriptTransformerConfig $config;

    public function __construct(TypeScriptTransformerConfig $config)
    {
        $this->config = $config;
    }

    public function execute(TypesCollection $collection): void
    {
        $this->ensureOutputFileExists();

        $writer = $this->config->getWriter();

        (new ReplaceSymbolsInCollectionAction())->execute(
            $collection,
            $writer->replacesSymbolsWithFullyQualifiedIdentifiers()
        );

        file_put_contents(
            $this->config->getOutputFile(),
            $writer->format($collection)
        );
    }

    protected function ensureOutputFileExists(): void
    {
        if (! file_exists(pathinfo($this->config->getOutputFile(), PATHINFO_DIRNAME))) {
            mkdir(pathinfo($this->config->getOutputFile(), PATHINFO_DIRNAME), 0755, true);
        }
    }
}
