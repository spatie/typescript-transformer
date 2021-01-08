<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Actions\ReplaceSymbolsInCollectionAction;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;

class ModuleWriter implements Writer
{
    public function format(TypesCollection $collection): string
    {
        $output = '';

        $iterator = $collection->getIterator();
        $iterator->uasort(function (TransformedType $a, TransformedType $b) {
            return strcmp($a->name, $b->name);
        });

        foreach ($iterator as $type) {
            if ($type->isInline) {
                continue;
            }

            $output .= $type->transformed.PHP_EOL;
        }

        return $output;
    }

    public function replaceMissingSymbols(TypesCollection $collection): self
    {
        (new ReplaceSymbolsInCollectionAction())->execute($collection, false);

        return $this;
    }
}
