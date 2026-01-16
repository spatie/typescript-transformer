<?php

namespace Spatie\TypeScriptTransformer\PhpNodes;

use ReflectionType;
use ReflectionUnionType;
use Roave\BetterReflection\Reflection\ReflectionType as RoaveReflectionType;
use Roave\BetterReflection\Reflection\ReflectionUnionType as RoaveReflectionUnionType;

/**
 * @property ReflectionUnionType|RoaveReflectionUnionType $reflection
 */
class PhpUnionTypeNode extends PhpTypeNode
{
    public function __construct(ReflectionUnionType|RoaveReflectionUnionType $reflection)
    {
        parent::__construct($reflection);
    }

    public function getTypes(): array
    {
        return array_map(fn (ReflectionType|RoaveReflectionType $type) => PhpTypeNode::fromReflection($type), $this->reflection->getTypes());
    }
}
