<?php

namespace Spatie\TypeScriptTransformer\Laravel\Routes;

class RouteClosure implements RouterStructure
{
    /**
     * @param array<string> $methods
     */
    public function __construct(
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
