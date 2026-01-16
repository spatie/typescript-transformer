<?php

namespace Spatie\TypeScriptTransformer\PhpNodes;

use ReflectionIntersectionType;
use ReflectionType;
use Roave\BetterReflection\Reflection\ReflectionIntersectionType as RoaveReflectionIntersectionType;
use Roave\BetterReflection\Reflection\ReflectionType as RoaveReflectionType;

/**
 * @property ReflectionIntersectionType|RoaveReflectionIntersectionType $reflection
 */
class PhpIntersectionTypeNode extends PhpTypeNode
{
    public function __construct(ReflectionIntersectionType|RoaveReflectionIntersectionType $reflection)
    {
        parent::__construct($reflection);
    }

    public function getTypes(): array
    {
        return array_map(fn (ReflectionType|RoaveReflectionType $type) => PhpTypeNode::fromReflection($type), $this->reflection->getTypes());
    }
}
