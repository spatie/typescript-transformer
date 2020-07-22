<?php

namespace Spatie\TypescriptTransformer\Steps;

use Spatie\TypescriptTransformer\Actions\ReplaceSymbolsInTypeAction;
use Spatie\TypescriptTransformer\Structures\Type;
use Spatie\TypescriptTransformer\Structures\TypesCollection;

class ReplaceMissingSymbolsStep
{
    public function execute(TypesCollection $collection): TypesCollection
    {
        $replaceSymbolsInTypeAction = new ReplaceSymbolsInTypeAction($collection);

        foreach ($collection->getTypes() as $type) {
            $type->transformed = $replaceSymbolsInTypeAction->execute($type);

            $collection[$type] = $type;
        }

        return $collection;
    }
}
