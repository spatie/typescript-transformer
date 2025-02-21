<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;
class ReplaceSymbolsInCollectionAction
{
    public function execute(TypesCollection $collection, $withFullyQualifiedNames = true): TypesCollection
    {
        $replaceSymbolsInTypeAction = TypeScriptTransformer::make(ReplaceSymbolsInTypeAction::class, $collection, $withFullyQualifiedNames);

        foreach ($collection as $type) {
            $type->transformed = $replaceSymbolsInTypeAction->execute($type);
        }

        return $collection;
    }
}
