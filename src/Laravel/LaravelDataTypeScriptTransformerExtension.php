<?php

namespace Spatie\TypeScriptTransformer\Laravel;

use Spatie\TypeScriptTransformer\Laravel\Transformers\DataClassTransformer;
use Spatie\TypeScriptTransformer\Support\Extensions\TypeScriptTransformerExtension;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;

class LaravelDataTypeScriptTransformerExtension implements TypeScriptTransformerExtension
{
    public function __construct(
        protected array $customLazyTypes = [],
        protected array $customDataCollections = [],
    ) {
    }

    public function enrich(TypeScriptTransformerConfigFactory $factory): void
    {
        $factory->extension(new LaravelTypeScriptTransformerExtension());

        $factory->prependTransformer(new DataClassTransformer(
            customLazyTypes: $this->customLazyTypes,
            customDataCollections: $this->customDataCollections,
        ));

        $factory->typesProvider(LaravelDataTypesProvider::class);
    }
}
