<?php

namespace Spatie\TypeScriptTransformer\Laravel\Routes;

readonly class RouteParameterCollection implements RouterStructure
{
    /**
     * @param array<RouteParameter> $parameters
     */
    public function __construct(
        public array $parameters,
    ) {
    }

    public function toJsObject(): array
    {
        return [];
    }
}
