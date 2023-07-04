<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\DefaultTypeProviders\DefaultTypesProvider;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class AppendDefaultTypesAction
{
    public function __construct(
        protected TypeScriptTransformerConfig $config,
        public TypeScriptTransformerLog $log,
    ) {
    }

    /**
     * @param array<Transformed> $transformed
     *
     * @return array<Transformed>
     */
    public function execute(array $transformed): array
    {
        $defaults = [];

        foreach ($this->config->defaultTypeProviders as $defaultTypeProviderClass) {
            /** @var DefaultTypesProvider $defaultTypeProvider */
            $defaultTypeProvider = new $defaultTypeProviderClass;

            array_push($defaults, ...$defaultTypeProvider->provide());
        }

        return array_merge($transformed, $defaults);
    }
}
