<?php

namespace Spatie\TypeScriptTransformer\Laravel;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Spatie\TypeScriptTransformer\Support\Extensions\TypeScriptTransformerExtension;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;

class LaravelTypeScriptTransformerExtension implements TypeScriptTransformerExtension
{
    public function enrich(TypeScriptTransformerConfigFactory $factory): void
    {
        $factory
            ->typesProvider(LaravelTypesProvider::class)
            ->replaceType(Collection::class, new TypeScriptIdentifier('Array'))
            ->replaceType(CarbonInterface::class, new TypeScriptString());
    }
}
