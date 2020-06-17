<?php

namespace Spatie\TypescriptTransformer;

use Spatie\TypescriptTransformer\Steps\PersistTypesCollectionStep;
use Spatie\TypescriptTransformer\Steps\ReplaceMissingSymbolsStep;
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
        $resolveTypesStep = new ResolveTypesStep(
            new Finder(),
            $this->config
        );

        $replaceMissingSymbolsStep = new ReplaceMissingSymbolsStep();

        $persistTypesCollectionStep = new PersistTypesCollectionStep($this->config);

        $collection = $resolveTypesStep->execute();

        $collection = $replaceMissingSymbolsStep->execute($collection);

        $persistTypesCollectionStep->execute($collection);
    }
}
