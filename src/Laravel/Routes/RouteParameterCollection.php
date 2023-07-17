<?php

namespace Spatie\TypeScriptTransformer\Laravel\Routes;

use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptObject;

class RouteParameterCollection implements RouterStructure
{
    /**
     * @param array<RouteParameter> $parameters
     */
    public function __construct(
        public array $parameters,
    ) {
    }

    public function toTypeScriptNode(): TypeScriptNode
    {
        return new TypeScriptObject(array_map(function (RouteParameter $parameter) {
            return $parameter->toTypeScriptNode();
        }, $this->parameters));
    }

    public function toJsObject(): array
    {
        return [];
    }
}
