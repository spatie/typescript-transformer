<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Structures\TypesCollection;

class ReplaceSymbolsInCollectionAction
{
    public function execute(TypesCollection $collection, $withFullyQualifiedNames = true): TypesCollection
    {
        $replaceSymbolsInTypeAction = new ReplaceSymbolsInTypeAction($collection, $withFullyQualifiedNames);

        foreach ($collection as $type) {
            $type->transformed = $replaceSymbolsInTypeAction->execute($type);
        }

        return $collection;
    }
}
