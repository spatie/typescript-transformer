<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Exceptions\CircularDependencyChain;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;

class ReplaceSymbolsInTypeAction
{
    protected TypesCollection $collection;

    public function __construct(TypesCollection $collection)
    {
        $this->collection = $collection;
    }

    public function execute(TransformedType $type, array $chain = []): string
    {
        if (in_array($type->getTypeScriptName(), $chain)) {
            $chain = array_merge($chain, [$type->getTypeScriptName()]);

            throw CircularDependencyChain::create($chain);
        }

        foreach ($type->missingSymbols->all() as $missingSymbol) {
            $this->collection[$type] = $this->replaceSymbol($missingSymbol, $type, $chain);
        }

        return $type->transformed;
    }

    protected function replaceSymbol(string $missingSymbol, TransformedType $type, array $chain): TransformedType
    {
        $found = $this->collection[$missingSymbol];

        if ($found === null) {
            $type->replaceSymbol($missingSymbol, 'any');

            return $type;
        }

        if (! $found->isInline) {
            $type->replaceSymbol($missingSymbol, $found->getTypeScriptName());

            return $type;
        }

        $transformed = $this->execute(
            $found,
            array_merge($chain, [$type->getTypeScriptName()])
        );

        $type->replaceSymbol($missingSymbol, $transformed);

        return $type;
    }
}
