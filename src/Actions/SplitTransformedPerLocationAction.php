<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Data\Location;
use Spatie\TypeScriptTransformer\Transformed\Transformed;

class SplitTransformedPerLocationAction
{
    /** @param array<Transformed> $transformed */
    public function execute(array $transformed): Location
    {
        $pointer = ['transformed' => [], 'children' => []];

        foreach ($transformed as $item) {
            $current = &$pointer;

            foreach ($item->location as $segment) {
                if (! array_key_exists($segment, $current['children'])) {
                    $current['children'][$segment] = ['transformed' => [], 'children' => []];
                }

                $current = &$current['children'][$segment];
            }

            $current['transformed'][] = $item;
        }

        return $this->buildLocation('', [], $pointer);
    }

    /**
     * @param array<string> $path
     * @param array{
     *     transformed: array<Transformed>,
     *     children: array<string, array>,
     * } $node
     */
    private function buildLocation(string $name, array $path, array $node): Location
    {
        $transformed = $node['transformed'];
        $childNames = array_keys($node['children']);

        usort($transformed, fn (Transformed $a, Transformed $b) => $a->getName() <=> $b->getName());
        sort($childNames);

        $children = array_map(
            fn (string $childName) => $this->buildLocation(
                $childName,
                [...$path, $childName],
                $node['children'][$childName],
            ),
            $childNames,
        );

        return new Location($name, $path, $transformed, $children);
    }
}
