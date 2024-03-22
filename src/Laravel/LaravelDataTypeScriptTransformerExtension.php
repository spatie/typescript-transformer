<?php

namespace Spatie\TypeScriptTransformer\Laravel;

use Spatie\TypeScriptTransformer\Laravel\Transformers\DataClassTransformer;
use Spatie\TypeScriptTransformer\Support\Extensions\TypeScriptTransformerExtension;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;

class LaravelDataTypeScriptTransformerExtension implements TypeScriptTransformerExtension
{
    public function enrich(TypeScriptTransformerConfigFactory $factory): void
    {
        $factory->transformer(DataClassTransformer::class);
    }
}
