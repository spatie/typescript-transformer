<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Support\Location;
use Spatie\TypeScriptTransformer\Transformed\Transformed;

class SplitTransformedPerLocationAction
{
    /**
     * @param array<Transformed> $transformed
     *
     * @return array<Location>
     */
    public function execute(array $transformed): array
    {
        $split = [];

        foreach ($transformed as $item) {
            $splitKey = count($item->location) > 0
                ? implode('.', $item->location)
                : '';

            if (! array_key_exists($splitKey, $split)) {
                $split[$splitKey] = new Location($item->location, []);
            }

            $split[$splitKey]->transformed[] = $item;
        }

        ksort($split);

        foreach ($split as $splitConstruct) {
            usort($splitConstruct->transformed, fn (Transformed $a, Transformed $b) => $a->getName() <=> $b->getName());
        }

        return array_values($split);
    }
}
