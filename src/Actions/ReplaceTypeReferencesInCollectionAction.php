<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Structures\TypesCollection;

class ReplaceTypeReferencesInCollectionAction
{
    public function execute(TypesCollection $collection, $withFullyQualifiedNames = true): TypesCollection
    {
        $replaceTypeReferencesInTypeAction = new ReplaceTypeReferencesInTypeAction($collection, $withFullyQualifiedNames);

        foreach ($collection as $type) {
            $type->transformed = $replaceTypeReferencesInTypeAction->execute($type);
        }

        return $collection;
    }
}
