<?php

namespace Spatie\TypeScriptTransformer\TypeReflectors;

use ReflectionMethod;
use ReflectionType;

class MethodReturnTypeReflector extends TypeReflector
{
    public static function create(ReflectionMethod $reflection): self
    {
        return new self($reflection);
    }

    public function __construct(ReflectionMethod $reflection)
    {
        parent::__construct($reflection);
    }

    protected function getDocblock(): string
    {
        return $this->reflection->getDocComment();
    }

    protected function docblockRegex(): string
    {
        return '/@return ((?:\s?[\w?|\\\\<>,-]+(?:\[])?)+)/';
    }

    protected function getReflectionType(): ?ReflectionType
    {
        return $this->reflection->getReturnType();
    }

    protected function getAttributes(): array
    {
        return $this->reflection->getAttributes();
    }
}
