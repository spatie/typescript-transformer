<?php

namespace Spatie\TypeScriptTransformer\PhpNodes;

use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use Roave\BetterReflection\Reflection\ReflectionIntersectionType as RoaveReflectionIntersectionType;
use Roave\BetterReflection\Reflection\ReflectionNamedType as RoaveReflectionNamedType;
use Roave\BetterReflection\Reflection\ReflectionType as RoaveReflectionType;
use Roave\BetterReflection\Reflection\ReflectionUnionType as RoaveReflectionUnionType;

class PhpTypeNode
{
    public function __construct(
        public readonly ReflectionType|RoaveReflectionType $reflection
    ) {
    }

    public static function fromReflection(
        ReflectionType|RoaveReflectionType $reflection
    ): PhpTypeNode {
        return match ($reflection::class) {
            ReflectionNamedType::class, RoaveReflectionNamedType::class => new PhpNamedTypeNode($reflection),
            ReflectionUnionType::class, RoaveReflectionUnionType::class => new PhpUnionTypeNode($reflection),
            ReflectionIntersectionType::class, RoaveReflectionIntersectionType::class => new PhpIntersectionTypeNode($reflection),
        };
    }

    public function allowsNull(): bool
    {
        return $this->reflection->allowsNull();
    }
}
