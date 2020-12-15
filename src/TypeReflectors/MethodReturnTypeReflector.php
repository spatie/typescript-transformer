<?php

namespace Spatie\TypeScriptTransformer\TypeReflectors;

use ReflectionMethod;
use ReflectionType;

class MethodReturnTypeReflector extends TypeReflector
{
    private ReflectionMethod $reflection;

    public static function create(ReflectionMethod $reflection): self
    {
        return new self($reflection);
    }

    public function __construct(ReflectionMethod $reflection)
    {
        $this->reflection = $reflection;
    }

    protected function getDocblock(): string
    {
        return $this->reflection->getDocComment();
    }

    protected function docblockRegex(): string
    {
        return '/@return ((?:(?:[\w?|\\\\<>,])+(?:\[])?)+)/';
    }

    protected function getReflectionType(): ?ReflectionType
    {
        return $this->reflection->getReturnType();
    }
}
