<?php

namespace Spatie\TypeScriptTransformer;

use Spatie\TypeScriptTransformer\Steps\PersistTypesCollectionStep;
use Spatie\TypeScriptTransformer\Steps\ResolveTypesStep;
use Spatie\TypeScriptTransformer\Actions\PersistTypesCollectionAction;
use Spatie\TypeScriptTransformer\Actions\ReplaceMissingSymbolsAction;
use Spatie\TypeScriptTransformer\Actions\ResolveTypesCollectionAction;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Symfony\Component\Finder\Finder;

class TypeScriptTransformer
{
    protected TypeScriptTransformerConfig $config;

    public static function create(TypeScriptTransformerConfig $config): self
    {
        return new self($config);
    }

    public function __construct(TypeScriptTransformerConfig $config)
    {
        $this->config = $config;
    }

    public function transform(): TypesCollection
    {
        $typesCollection = (new ResolveTypesCollectionAction(
            new Finder(),
            $this->config,
        ))->execute();

        (new PersistTypesCollectionStep($this->config))->execute($typesCollection);

        return $typesCollection;
    }
}
