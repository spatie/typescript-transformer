<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class ReplaceSymbolsInCollectionAction
{
    private TypeScriptTransformerConfig $config;

    public function __construct(TypeScriptTransformerConfig $config)
    {
        $this->config = $config;
    }
    public function execute(TypesCollection $collection, $withFullyQualifiedNames = true): TypesCollection
    {
        $replaceSymbolsInTypeAction = new ReplaceSymbolsInTypeAction($this->config, $collection, $withFullyQualifiedNames);

        foreach ($collection as $type) {
            $type->transformed = $replaceSymbolsInTypeAction->execute($type);
        }

        return $collection;
    }
}
