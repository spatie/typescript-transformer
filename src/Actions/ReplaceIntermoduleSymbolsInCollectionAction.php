<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Structures\TranspilationResult;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;

class ReplaceIntermoduleSymbolsInCollectionAction
{
    public function execute(
        TypesCollection $moduleCollection,
        TypesCollection $totalCollection,
        $withFullyQualifiedNames = true
    ): TypesCollection
    {
        $replaceSymbolsInTypeAction = new ReplaceSymbolsInTypeAction($totalCollection, $withFullyQualifiedNames);

        foreach ($moduleCollection as $type) {
            $type->transformed = $replaceSymbolsInTypeAction->execute($type);
        }

        return $moduleCollection;
    }
}
