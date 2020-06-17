<?php

namespace Spatie\TypescriptTransformer\Steps;

use ReflectionClass;
use Spatie\TypescriptTransformer\Structures\Collection;
use Spatie\TypescriptTransformer\Structures\Type;

class ReplaceMissingSymbolsStep
{
    public function execute(Collection $collection): Collection
    {
        $collection->map(fn (Type $type) => $this->resolveMissingSymbols(
            $collection,
            $type
        ));

        return $collection;
    }

    private function resolveMissingSymbols(Collection $collection, Type $type): Type
    {
        $missingSymbols = [];

        foreach ($type->missingSymbols as $symbol) {
            $foundSymbol = $collection->find($symbol);

            $missingSymbols[$symbol] = $foundSymbol !== null
                ? $foundSymbol->getTypescriptName()
                : 'any';
        }

        foreach ($missingSymbols as $symbol => $replacement) {
            $type->transformed = str_replace(
                "{%{$symbol}%}",
                $replacement,
                $type->transformed
            );
        }

        return $type;
    }
}
