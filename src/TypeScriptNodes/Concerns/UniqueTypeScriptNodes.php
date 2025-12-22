<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes\Concerns;

trait UniqueTypeScriptNodes
{
    protected function uniqueNodes(array $nodes): array
    {
        $unique = [];

        foreach ($nodes as $node) {
            foreach ($unique as $uniqueNode) {
                if ($node == $uniqueNode) { // Using loose comparison to determine uniqueness
                    continue 2;
                }
            }

            $unique[] = $node;
        }

        return $unique;
    }
}
