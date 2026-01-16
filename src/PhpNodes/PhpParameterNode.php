<?php

namespace Spatie\TypeScriptTransformer\PhpNodes;

use ReflectionParameter;
use Roave\BetterReflection\Reflection\ReflectionParameter as RoaveReflectionParameter;

class PhpParameterNode
{
    public function __construct(
        public ReflectionParameter|RoaveReflectionParameter $reflection
    ) {
    }

    public function getName(): string
    {
        return $this->reflection->getName();
    }

    public function hasType(): bool
    {
        return $this->reflection->hasType();
    }

    public function getType(): ?PhpTypeNode
    {
        $type = $this->reflection->getType();

        if ($type === null) {
            return null;
        }

        return PhpTypeNode::fromReflection($type);
    }

    public function isOptional(): bool
    {
        return $this->reflection->isOptional();
    }
}
