<?php

namespace Spatie\TypeScriptTransformer\TypeReflectors;

use ReflectionProperty;
use ReflectionType;

class PropertyTypeReflector extends TypeReflector
{
    public static function create(ReflectionProperty $reflection): self
    {
        return new self($reflection);
    }

    public function __construct(ReflectionProperty $reflection)
    {
        parent::__construct($reflection);
    }

    protected function getDocblock(): string
    {
        return $this->reflection->getDocComment();
    }

    protected function docblockRegex(): string
    {
        return '/@var ((?:\s?[\\w?|\\\\<>,-]+(?:\[])?)+)/';
    }

    protected function getReflectionType(): ?ReflectionType
    {
        return $this->reflection->getType();
    }

    protected function getAttributes(): array
    {
        return $this->reflection->getAttributes();
    }
}
