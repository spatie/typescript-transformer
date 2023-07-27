<?php

namespace Spatie\TypeScriptTransformer\Laravel\Routes;

class RouteInvokableController implements RouterStructure
{
    /**
     * @param  array<string>  $methods
     */
    public function __construct(
        public RouteParameterCollection $parameters,
        public array $methods,
        public string $url,
        public ?string $name,
    ) {
    }
}
