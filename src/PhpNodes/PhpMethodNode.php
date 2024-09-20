<?php

namespace Spatie\TypeScriptTransformer\PhpNodes;

use ReflectionMethod;
use ReflectionParameter;
use Roave\BetterReflection\Reflection\ReflectionMethod as RoaveReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionParameter as RoaveReflectionParameter;

class PhpMethodNode
{
    public function __construct(
        public readonly ReflectionMethod|RoaveReflectionMethod $reflection
    ) {
    }

    public function getDocComment(): ?string
    {
        return $this->reflection->getDocComment() ?: null;
    }

    public function getName(): string
    {
        return $this->reflection->getName();
    }

    public function getReturnType(): ?PhpTypeNode
    {
        $type = $this->reflection->getReturnType();

        if ($type === null) {
            return null;
        }

        return PhpTypeNode::fromReflection($type);
    }

    public function getParameters(): array
    {
        return array_map(
            fn (ReflectionParameter|RoaveReflectionParameter $parameter) => new PhpParameterNode($parameter),
            $this->reflection->getParameters(),
        );
    }
}
