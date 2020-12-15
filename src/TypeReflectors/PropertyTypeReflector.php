<?php

namespace Spatie\TypeScriptTransformer\TypeReflectors;

use ReflectionProperty;
use ReflectionType;

class PropertyTypeReflector extends TypeReflector
{
    private ReflectionProperty $reflection;

    public static function create(ReflectionProperty $reflection): self
    {
        return new self($reflection);
    }

    public function __construct(ReflectionProperty $reflection)
    {
        $this->reflection = $reflection;
    }

    protected function getDocblock(): string
    {
        return $this->reflection->getDocComment();
    }

    protected function docblockRegex(): string
    {
        return '/@var ((?:(?:[\w?|\\\\<>,])+(?:\[])?)+)/';
    }

    protected function getReflectionType(): ?ReflectionType
    {
        return $this->reflection->getType();
    }
}
