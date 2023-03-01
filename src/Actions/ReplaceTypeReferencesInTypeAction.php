<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Exceptions\CircularDependencyChain;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\Structures\TypeReference;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;

class ReplaceTypeReferencesInTypeAction
{
    protected TypesCollection $collection;

    protected bool $withFullyQualifiedNames;

    public function __construct(TypesCollection $collection, $withFullyQualifiedNames = true)
    {
        $this->collection = $collection;
        $this->withFullyQualifiedNames = $withFullyQualifiedNames;
    }

    public function execute(TransformedType $type, array $chain = []): string
    {
        if (in_array($type->getTypeScriptName(), $chain)) {
            $chain = array_merge($chain, [$type->getTypeScriptName()]);

            throw CircularDependencyChain::create($chain);
        }

        foreach ($type->typeReferences as $typeReference) {
            $this->collection->add(
                $this->replaceSymbol($typeReference, $type, $chain)
            );
        }

        return $type->transformed;
    }

    protected function replaceSymbol(
        TypeReference $typeReference,
        TransformedType $type,
        array $chain
    ): TransformedType
    {
        $found = $this->collection->get(
            $typeReference->getFqcn()
        );

        if ($found === null) {
            $type->replaceTypeReference($typeReference, 'any');

            return $type;
        }

        if (! $found->isInline) {
            $type->replaceTypeReference($typeReference, $found->getTypeScriptName($this->withFullyQualifiedNames));

            return $type;
        }

        $transformed = $this->execute(
            $found,
            array_merge($chain, [$type->getTypeScriptName()])
        );

        $type->replaceTypeReference($typeReference, $transformed);

        return $type;
    }
}
