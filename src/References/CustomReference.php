<?php

namespace Spatie\TypeScriptTransformer\References;

class CustomReference implements Reference
{
    public function __construct(
        public string $group,
        public string $name,
    ) {
    }

    public function getKey(): string
    {
        return "custom_{$this->group}_{$this->name}";
    }

    public function humanFriendlyName(): string
    {
        return "custom {$this->group}::{$this->name}";
    }
}
