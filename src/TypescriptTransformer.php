<?php

namespace Spatie\TypescriptTransformer;

use Spatie\TypescriptTransformer\Steps\PersistTypesCollectionStep;
use Spatie\TypescriptTransformer\Steps\ReplaceMissingSymbolsStep;
use Spatie\TypescriptTransformer\Steps\ResolveTypesStep;
use Spatie\TypescriptTransformer\Structures\TypesCollection;
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

    public function transform(): TypesCollection
    {
        $typesCollection = (new ResolveTypesStep(
            new Finder(),
            $this->config,
        ))->execute();

        (new ReplaceMissingSymbolsStep())->execute($typesCollection);

        (new PersistTypesCollectionStep($this->config))->execute($typesCollection);

        return $typesCollection;
    }
}
