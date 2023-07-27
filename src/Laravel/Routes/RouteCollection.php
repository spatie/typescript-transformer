<?php

namespace Spatie\TypeScriptTransformer\Laravel\Routes;

class RouteCollection implements RouterStructure
{
    /**
     * @param  array<string, RouteController|RouteInvokableController>  $controllers
     * @param  array<string, RouteClosure>  $closures
     */
    public function __construct(
        public array $controllers,
        public array $closures,
    ) {
    }

    public function toJsObject(): array
    {
        return [
            'controllers' => collect($this->controllers)->map(fn (RouteController|RouteInvokableController $controller) => $controller->toJsObject())->all(),
            'closures' => collect($this->closures)->map(fn (RouteClosure $closure) => $closure->toJsObject())->all(),
        ];
    }
}
