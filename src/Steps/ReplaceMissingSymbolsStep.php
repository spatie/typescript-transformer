<?php

namespace Spatie\TypeScriptTransformer\Steps;

use Spatie\TypeScriptTransformer\Actions\ReplaceSymbolsInTypeAction;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;

class ReplaceMissingSymbolsStep
{
    public function execute(TypesCollection $collection): TypesCollection
    {
        $replaceSymbolsInTypeAction = new ReplaceSymbolsInTypeAction($collection);

        foreach ($collection as $type) {
            $type->transformed = $replaceSymbolsInTypeAction->execute($type);
        }

        return $collection;
    }
}
