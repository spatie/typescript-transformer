<?php

namespace Spatie\TypeScriptTransformer\TypeProviders;

use Spatie\TypeScriptTransformer\Actions\DiscoverTypesAction;
use Spatie\TypeScriptTransformer\Actions\TransformTypesAction;
use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\Transformers\Transformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class TransformerTypesProvider implements TypesProvider
{
    /**
     * @param array<Transformer> $transformers
     * @param array<string> $directories
     */
    public function __construct(
        protected array $transformers,
        protected array $directories,
    ) {
    }

    public function provide(
        TypeScriptTransformerConfig $config,
        TransformedCollection $types
    ): void {
        $transformTypesAction = new TransformTypesAction();
        $discoverTypesAction = new DiscoverTypesAction();

        $types->add(...$transformTypesAction->execute(
            $this->transformers,
            $discoverTypesAction->execute($this->directories),
        ));
    }
}
