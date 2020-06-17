<?php

namespace Spatie\TypescriptTransformer;

use Spatie\TypescriptTransformer\Steps\PersistTypesCollectionAction;
use Spatie\TypescriptTransformer\Steps\ResolveTypesStep;
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
        $typesResolver = new ResolveTypesStep(
            new Finder(),
            $this->config
        );

        $typesPersister = new PersistTypesCollectionAction($this->config);

        $typesPersister->execute(
            $typesResolver->execute()
        );
    }
}
