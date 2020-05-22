<?php

namespace Spatie\TypescriptTransformer;

use Spatie\TypescriptTransformer\Actions\PersistTypesCollectionAction;
use Spatie\TypescriptTransformer\Actions\ResolveTypesCollectionAction;
use Symfony\Component\Finder\Finder;

class TypescriptTransformer
{
    private TypeScriptTransformerConfig $config;

    public static function create(TypeScriptTransformerConfig $config): self
    {
        return new self($config);
    }

    public function __construct(TypeScriptTransformerConfig $config)
    {
        $this->config = $config;
    }

    public function transform()
    {
        $typesResolver = new ResolveTypesCollectionAction(
            new Finder(),
            $this->config
        );

        $typesPersister = new PersistTypesCollectionAction($this->config);

        $typesPersister->execute(
            $typesResolver->execute()
        );
    }
}
