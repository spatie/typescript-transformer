<?php

namespace Spatie\TypeScriptTransformer\Laravel\References;

use Spatie\TypeScriptTransformer\References\Reference;

abstract class LaravelRouteReference implements Reference
{
    final protected function __construct(
        protected string $key,
    ) {
    }

    public function getKey(): string
    {
        return "{$this->getKind()}::{$this->key}";
    }

    public function humanFriendlyName(): string
    {
        return "{$this->getKind()}::{$this->key}";
    }

    abstract protected function getKind(): string;

    public static function list(): static
    {
        return new static('list');
    }

    public static function function(): static
    {
        return new static('function');
    }
}
