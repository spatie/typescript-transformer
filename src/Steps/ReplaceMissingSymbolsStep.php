<?php

namespace Spatie\TypescriptTransformer\Steps;

use Spatie\TypescriptTransformer\Actions\ReplaceSymbolsInTypeAction;
use Spatie\TypescriptTransformer\Structures\TypesCollection;
use Spatie\TypescriptTransformer\Structures\Type;

class ReplaceMissingSymbolsStep
{
    public function execute(TypesCollection $collection): TypesCollection
    {
        $replaceSymbolsInTypeAction = new ReplaceSymbolsInTypeAction($collection);

        $collection->map(function (Type $type) use ($replaceSymbolsInTypeAction) {
            $type->transformed = $replaceSymbolsInTypeAction->execute($type);

            return $type;
        });

        return $collection;
    }
}
