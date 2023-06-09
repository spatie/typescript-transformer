<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Exceptions\CircularDependencyChain;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class ReplaceSymbolsInTypeAction
{
    protected TypesCollection $collection;

    protected bool $withFullyQualifiedNames;
    private TypeScriptTransformerConfig $config;

    public function __construct(TypeScriptTransformerConfig $config, TypesCollection $collection, $withFullyQualifiedNames = true)
    {
        $this->collection = $collection;
        $this->withFullyQualifiedNames = $withFullyQualifiedNames;
        $this->config = $config;
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
        if (!$found && $this->config->fuzzyTypeSearchEnabled()) {
            $found = $this->collection->getTypeByShortName($missingSymbol);
        }

        if ($found === null) {
            $type->replaceSymbol($missingSymbol, 'any');

            return $type;
        }

        if (!$found->isInline) {
            $type->replaceSymbol($missingSymbol, $found->getTypeScriptName($this->withFullyQualifiedNames));

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
