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

            throw new Exception("Circular dependency chain found: ". implode(' -> ', $chain));
        }

        foreach ($type->missingSymbols as $missingSymbol) {
            $this->collection->replace(
                $this->replaceSymbol($missingSymbol, $type, $chain)
            );
        }

        return $type->transformed;
    }

    private function replaceSymbol(string $missingSymbol, Type $type, array $chain): Type
    {
        $found = $this->collection->find($missingSymbol);

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
