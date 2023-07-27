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
}
