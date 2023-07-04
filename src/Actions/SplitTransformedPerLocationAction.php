<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Support\Location;
use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\Transformed\Transformed;

class SplitTransformedPerLocationAction
{
    /**
     * @return array<Location>
     */
    public function execute(TransformedCollection $collection): array
    {
        $split = [];

        foreach ($collection as $transformed) {
            $splitKey = count($transformed->location) > 0
                ? implode('.', $transformed->location)
                : '';

            if (! array_key_exists($splitKey, $split)) {
                $split[$splitKey] = new Location($transformed->location, []);
            }

            $split[$splitKey]->transformed[] = $transformed;
        }

        ksort($split);

        foreach ($split as $splitConstruct) {
            usort($splitConstruct->transformed, fn (Transformed $a, Transformed $b) => $a->name <=> $b->name);
        }

        return array_values($split);
    }
}
