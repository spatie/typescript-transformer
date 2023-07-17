<?php

namespace Spatie\TypeScriptTransformer\Laravel\Routes;

use Spatie\TypeScriptTransformer\TypeScript\TypeScriptLiteral;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptProperty;

class RouteControllerAction implements RouterStructure
{
    /**
     * @param array<string> $methods
     */
    public function __construct(
        public string $name,
        public RouteParameterCollection $parameters,
        public array $methods,
        public string $url,
    ) {
    }

    public function toTypeScriptNode(): TypeScriptNode
    {
        return new TypeScriptObject([
            new TypeScriptProperty('name', new TypeScriptLiteral($this->name)),
            new TypeScriptProperty('parameters', $this->parameters->toTypeScriptNode()),
        ]);
    }

    public function toJsObject(): array
    {
        return [
            'url' => $this->url,
            'methods' => array_values($this->methods),
        ];
    }
}
