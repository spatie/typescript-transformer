<?php

namespace Spatie\TypeScriptTransformer\PhpNodes;

use ReflectionEnumBackedCase;
use ReflectionEnumUnitCase;
use Roave\BetterReflection\Reflection\ReflectionEnumCase;

class PhpEnumCaseNode
{
    public function __construct(
        public readonly ReflectionEnumBackedCase|ReflectionEnumUnitCase|ReflectionEnumCase $reflection
    ) {
    }

    public function getName(): string
    {
        return $this->reflection->getName();
    }

    public function getValue(): string|int|null
    {
        if ($this->reflection instanceof ReflectionEnumCase) {
            return $this->reflection->getValue();
        }

        if (! method_exists($this->reflection, 'getBackingValue')) {
            return null;
        }

        return $this->reflection->getBackingValue();
    }
}
