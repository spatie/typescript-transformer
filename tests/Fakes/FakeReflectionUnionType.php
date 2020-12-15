<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes;

use ReflectionUnionType;

class FakeReflectionUnionType extends ReflectionUnionType
{
    /** @var \Spatie\TypeScriptTransformer\Tests\Fakes\FakeReflectionType[] */
    private array $types = [];

    public static function create(): self
    {
        return new self();
    }

    public function __construct()
    {
    }

    public function withType(...$type): self
    {
        $this->types = array_merge($this->types, $type);

        return $this;
    }

    public function getTypes(): array
    {
        return $this->types;
    }

    public function allowsNull(): bool
    {
        foreach ($this->types as $type) {
            if ($type->allowsNull()) {
                return true;
            }
        }

        return false;
    }
}
