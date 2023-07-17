<?php

namespace Spatie\TypeScriptTransformer\Laravel\Routes;

use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNumber;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptUnion;

class RouteParameter implements RouterStructure
{
    public function __construct(
        public string $name,
        public bool $optional,
    ) {
    }

    public function toTypeScriptNode(): TypeScriptNode
    {
        return new TypeScriptProperty(
            $this->name,
            new TypeScriptUnion([new TypeScriptString(), new TypeScriptNumber()]),
            isOptional: $this->optional,
        );
    }

    public function toJsObject(): array
    {
        return [];
    }
}
