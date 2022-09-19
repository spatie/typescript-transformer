<?php

namespace Spatie\TypeScriptTransformer\TypeReflectors;

use ReflectionParameter;
use ReflectionType;

class MethodParameterTypeReflector extends TypeReflector
{
    public static function create(ReflectionParameter $reflection): self
    {
        return new self($reflection);
    }

    public function __construct(ReflectionParameter $reflection)
    {
        parent::__construct($reflection);
    }

    protected function getDocblock(): string
    {
        return $this->reflection->getDeclaringFunction()->getDocComment();
    }

    protected function docblockRegex(): string
    {
        return "/@param ((?:\\s?[\\w?|\\\\<>,-]+(?:\\[])?)+) \\\${$this->reflection->getName()}/";
    }

    protected function getReflectionType(): ?ReflectionType
    {
        return $this->reflection->getType();
    }

    protected function getAttributes(): array
    {
        return [];
    }
}
