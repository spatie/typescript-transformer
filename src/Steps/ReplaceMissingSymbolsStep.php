<?php

namespace Spatie\TypescriptTransformer\Steps;

use Spatie\TypescriptTransformer\Actions\ReplaceSymbolsInTypeAction;
use Spatie\TypescriptTransformer\Structures\Collection;
use Spatie\TypescriptTransformer\Structures\Type;

class ReplaceMissingSymbolsStep
{
    public function execute(Collection $collection): Collection
    {
        $replaceSymbolsInTypeAction = new ReplaceSymbolsInTypeAction($collection);

        $collection->map(function (Type $type) use ($replaceSymbolsInTypeAction) {
            $type->transformed = $replaceSymbolsInTypeAction->execute($type);

            return $type;
        });

        return $collection;
    }
}
