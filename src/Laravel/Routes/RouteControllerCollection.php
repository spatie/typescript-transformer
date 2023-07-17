<?php

namespace Spatie\TypeScriptTransformer\Laravel\Routes;

use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptProperty;

class RouteControllerCollection implements RouterStructure
{
    /**
     * @param  array<string, RouteController|InvokableRouteController>  $controllers
     */
    public function __construct(
        public array $controllers
    ) {
    }

    public function toTypeScriptNode(): TypeScriptNode
    {
        return new TypeScriptObject(collect($this->controllers)->map(function (RouteController|InvokableRouteController $controller, string $name) {
            return new TypeScriptProperty(
                $name,
                $controller->toTypeScriptNode(),
            );
        })->all());
    }

    public function toJsObject(): array
    {
        return collect($this->controllers)->map(function (RouteController|InvokableRouteController $controller) {
            return $controller->toJsObject();
        })->all();
    }
}
