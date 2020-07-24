<?php

namespace Spatie\TypescriptTransformer\Actions;

use Exception;
use Spatie\TypescriptTransformer\Structures\Type;
use Spatie\TypescriptTransformer\Structures\TypesCollection;

class ReplaceSymbolsInTypeAction
{
    private TypesCollection $collection;

    public function __construct(TypesCollection $collection)
    {
        $this->collection = $collection;
    }

    public function execute(Type $type, array $chain = []): string
    {
        if (in_array($type->getTypescriptName(), $chain)) {
            $chain = array_merge($chain, [$type->getTypescriptName()]);

            /** TODO: use dedicated exception */
            throw new Exception("Circular dependency chain found: ". implode(' -> ', $chain));
        }

        foreach ($type->missingSymbols->all() as $missingSymbol) {
            $this->collection[$type] = $this->replaceSymbol($missingSymbol, $type, $chain);
        }

        return $type->transformed;
    }

    private function replaceSymbol(string $missingSymbol, Type $type, array $chain): Type
    {
        $found = $this->collection[$missingSymbol];

        if ($found === null) {
            $type->replaceSymbol($missingSymbol, 'any');

            return $type;
        }

        if (! $found->isInline) {
            $type->replaceSymbol($missingSymbol, $found->getTypescriptName());

            return $type;
        }

        $transformed = $this->execute(
            $found,
            array_merge($chain, [$type->getTypescriptName()])
        );

        $type->replaceSymbol($missingSymbol, $transformed);

        return $type;
    }
}
