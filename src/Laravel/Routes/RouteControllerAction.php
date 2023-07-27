<?php

namespace Spatie\TypeScriptTransformer\Laravel\Routes;

class RouteControllerAction implements RouterStructure
{
    /**
     * @param  array<string>  $methods
     */
    public function __construct(
        public string $name,
        public RouteParameterCollection $parameters,
        public array $methods,
        public string $url,
    ) {
    }

    public function toJsObject(): array
    {
        return [
            'url' => $this->url,
            'methods' => array_values($this->methods),
        ];
    }
}
