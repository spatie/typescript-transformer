<?php

namespace Spatie\TypeScriptTransformer\PhpNodes;

use ReflectionNamedType;
use Roave\BetterReflection\Reflection\ReflectionNamedType as RoaveReflectionNamedType;

/**
 * @property ReflectionNamedType|RoaveReflectionNamedType $reflection
 */
class PhpNamedTypeNode extends PhpTypeNode
{
    public function __construct(ReflectionNamedType|RoaveReflectionNamedType $reflection)
    {
        parent::__construct($reflection);
    }

    public function getName(): string
    {
        return $this->reflection->getName();
    }
}
