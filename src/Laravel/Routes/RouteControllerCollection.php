<?php

namespace Spatie\TypeScriptTransformer\Laravel\Routes;

use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptProperty;

readonly  class RouteControllerCollection implements RouterStructure
{
    /**
     * @param  array<string, RouteController|RouteInvokableController>  $controllers
     */
    public function __construct(
        public array $controllers
    ) {
    }

    public function toJsObject(): array
    {
        return collect($this->controllers)->map(function (RouteController|RouteInvokableController $controller) {
            return $controller->toJsObject();
        })->all();
    }
}
