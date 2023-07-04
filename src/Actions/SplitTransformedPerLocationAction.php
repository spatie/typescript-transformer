<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Support\Location;
use Spatie\TypeScriptTransformer\Transformed\Transformed;

class SplitTransformedPerLocationAction
{
    /**
     * @param  array<Transformed> $transformedTypes
     *
     * @return array<Location>
     */
    public function execute(array $transformedTypes): array
    {
        $split = [];

        foreach ($transformedTypes as $transformedType) {
            $splitKey = count($transformedType->location) > 0
                ? implode('.', $transformedType->location)
                : '';

            if (! array_key_exists($splitKey, $split)) {
                $split[$splitKey] = new Location($transformedType->location, []);
            }

            $split[$splitKey]->transformed[] = $transformedType;
        }

        ksort($split);

        foreach ($split as $splitConstruct) {
            usort($splitConstruct->transformed, fn (Transformed $a, Transformed $b) => $a->name <=> $b->name);
        }

        return array_values($split);
    }
}
