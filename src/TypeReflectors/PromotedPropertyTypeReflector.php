<?php

namespace Spatie\TypeScriptTransformer\TypeReflectors;

use ReflectionProperty;
use ReflectionType;

class PromotedPropertyTypeReflector extends TypeReflector
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
        return $this->reflection->getDeclaringClass()->getMethod('__construct')->getDocComment();
    }

    protected function docblockRegex(): string
    {
        $parameterName = $this->reflection->getName();
        return '/@param\s+([^\s<]+(?:<[^>]*>)?)\s+\$' . preg_quote($parameterName, '/') . '\b/m';

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
