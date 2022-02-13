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
        $writer = $this->config->getWriter();

        (new ReplaceSymbolsInCollectionAction())->execute(
            $collection,
            $writer->replacesSymbolsWithFullyQualifiedIdentifiers()
        );

        $writer->format($collection);
    }
}
