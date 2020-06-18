<?php

namespace Spatie\TypescriptTransformer\Steps;

use Spatie\TypescriptTransformer\Structures\Collection;
use Spatie\TypescriptTransformer\Structures\Type;

class ReplaceTypesStep
{
    public function execute(Collection $collection)
    {
        $collection->map(fn(Type $type) => $this->resolveMissingSymbols(
            $collection,
            $type
        ));

        return $collection;
    }

    private function resolveMissingSymbols(Collection $collection, Type $type): Type
    {
        if (count($type->missingSymbols)) {

        }
    }
}
