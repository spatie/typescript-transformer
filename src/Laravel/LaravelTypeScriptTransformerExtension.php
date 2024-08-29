<?php

namespace Spatie\TypeScriptTransformer\Laravel;

use Carbon\CarbonInterface;
use Spatie\TypeScriptTransformer\Laravel\Transformers\LaravelAttributedClassTransformer;
use Spatie\TypeScriptTransformer\Support\Extensions\TypeScriptTransformerExtension;
use Spatie\TypeScriptTransformer\Transformers\AttributedClassTransformer;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;

class LaravelTypeScriptTransformerExtension implements TypeScriptTransformerExtension
{
    public function enrich(TypeScriptTransformerConfigFactory $factory): void
    {
        $factory
            ->replaceTransformer(
                AttributedClassTransformer::class,
                LaravelAttributedClassTransformer::class
            )
            ->typesProvider(LaravelTypesProvider::class)
            ->replaceType(CarbonInterface::class, new TypeScriptString());
    }
}
