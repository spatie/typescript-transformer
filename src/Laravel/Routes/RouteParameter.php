<?php

namespace Spatie\TypeScriptTransformer\Laravel\Routes;

class RouteParameter implements RouterStructure
{
    public function __construct(
        public string $name,
        public bool $optional,
    ) {
    }
}
