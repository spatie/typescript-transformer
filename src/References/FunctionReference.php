<?php

namespace Spatie\TypeScriptTransformer\References;

class FunctionReference implements Reference
{
    public function __construct(
        public string $name,
    ) {
    }

    public function getKey(): string
    {
        return "function_{$this->name}";
    }

    public function humanFriendlyName(): string
    {
        return "function {$this->name}";
    }
}
