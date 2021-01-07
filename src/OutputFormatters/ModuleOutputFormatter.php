<?php


namespace Spatie\TypeScriptTransformer\OutputFormatters;

use Spatie\TypeScriptTransformer\Actions\ReplaceSymbolsInCollectionAction;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;

class ModuleOutputFormatter implements OutputFormatter
{
    public function format(TypesCollection $collection): string
    {
        $output = '';

        foreach ($collection as $type) {
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
