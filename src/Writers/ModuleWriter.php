<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;

class ModuleWriter implements Writer
{
    public function format(TypesCollection $collection): string
    {
        $output = '';

        /** @var \ArrayIterator $iterator */
        $iterator = $collection->getIterator();

        $iterator->uasort(function (TransformedType $a, TransformedType $b) {
            return strcmp($a->name, $b->name);
        });

        foreach ($iterator as $type) {
            if ($type->isInline) {
                continue;
            }

            $output .= "export {$type->toString()}".PHP_EOL;
        }

        return $output;
    }

    public function replacesSymbolsWithFullyQualifiedIdentifiers(): bool
    {
        return false;
    }
}
