<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes;

use ReflectionNamedType;
use ReflectionType;

class FakeReflectionType extends ReflectionNamedType
{
    private ?string $type = null;

    private bool $isBuiltIn = true;

    private bool $allowsNull = false;

    public static function create(): self
    {
        return new self();
    }

    public function __construct()
    {
    }

    public function withType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function withIsBuiltIn(bool $isBuiltIn = true): self
    {
        $this->isBuiltIn = $isBuiltIn;

        return $this;
    }

    public function withAllowsNull(bool $allowsNull = true): self
    {
        $this->allowsNull = $allowsNull;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->type;
    }

    public function isBuiltin()
    {
        return $this->isBuiltIn;
    }

    public function allowsNull()
    {
        return $this->allowsNull;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
