<?php

namespace Spatie\TypeScriptTransformer\TransformedProviders;

use Spatie\TypeScriptTransformer\Actions\DiscoverTypesAction;
use Spatie\TypeScriptTransformer\Actions\TransformTypesAction;
use Spatie\TypeScriptTransformer\Transformers\Transformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class TransformerProvider implements TransformedProvider
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
    ): array {
        $transformTypesAction = new TransformTypesAction();
        $discoverTypesAction = new DiscoverTypesAction();

        return $transformTypesAction->execute(
            $this->transformers,
            $discoverTypesAction->execute($this->directories),
        );
    }
}
