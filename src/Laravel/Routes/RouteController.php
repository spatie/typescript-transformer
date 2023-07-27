<?php

namespace Spatie\TypeScriptTransformer\Laravel\Routes;

class RouteController implements RouterStructure
{
    /**
     * @param  array<string, RouteControllerAction>  $actions
     */
    public function __construct(
        public array $actions,
    ) {
    }
}
