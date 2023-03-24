<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Structures\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;

class ModuleWriter implements Writer
{
    public function format(TypesCollection $collection): string
    {
        $output = '';

        /** @var \ArrayIterator $iterator */
        $iterator = $collection->getIterator();

        $iterator->uasort(function (Transformed $a, Transformed $b) {
            return strcmp($a->name->getTypeScriptName(), $b->name->getTypeScriptName());
        });

        foreach ($iterator as $type) {
            if ($type->inline) {
                continue;
            }

            $output .= "export {$type->toString()}" . PHP_EOL;
        }

        return $output;
    }

    public function replacesSymbolsWithFullyQualifiedIdentifiers(): bool
    {
        return false;
    }
}
