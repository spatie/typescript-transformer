<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Exceptions\CircularDependencyChain;
use Spatie\TypeScriptTransformer\Structures\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Structures\TypeReference;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;

class ReplaceTypeReferencesInTypeAction
{
    public function __construct(
        protected TypesCollection $collection,
        protected bool $withFullyQualifiedNames = true
    ) {
    }

    public function execute(Transformed $type, array $chain = []): void
    {
        if (in_array($type->name->getTypeScriptName(), $chain)) {
            $chain = array_merge($chain, [$type->name->getTypeScriptName()]);

            throw CircularDependencyChain::create($chain);
        }

        foreach ($type->typeReferences as $typeReference) {
            $this->collection->add(
                $this->replaceSymbol($typeReference, $type, $chain)
            );
        }
    }

    protected function replaceSymbol(
        TypeReference $typeReference,
        Transformed $type,
        array $chain
    ): Transformed {
        $found = $this->collection->get(
            $typeReference->getFqcn()
        );

        $typeReference->referenced = $found;

        if ($found === null) {
            $type->replaceTypeReference($typeReference, 'any');

            return $type;
        }

        if (! $found->inline) {
            $type->replaceTypeReference(
                $typeReference,
                $this->withFullyQualifiedNames
                    ? $found->name->getTypeScriptFqcn()
                    : $found->name->name,
            );

            return $type;
        }

        $this->execute(
            $found,
            array_merge($chain, [$type->name->getTypeScriptName()])
        );

        $type->replaceTypeReference($typeReference, $found->toString());

        return $type;
    }
}
