<?php

namespace Spatie\TypeScriptTransformer\PhpNodes;

use ReflectionEnum;
use ReflectionEnumBackedCase;
use ReflectionEnumUnitCase;
use Roave\BetterReflection\Reflection\ReflectionEnum as RoaveReflectionEnum;
use Roave\BetterReflection\Reflection\ReflectionEnumCase;

/**
 * @property ReflectionEnum|RoaveReflectionEnum $reflection
 */
class PhpEnumNode extends PhpClassNode
{
    public function __construct(ReflectionEnum|RoaveReflectionEnum $reflection)
    {
        parent::__construct($reflection);
    }

    public function isBacked(): bool
    {
        return $this->reflection->isBacked();
    }

    /**
     * @return PhpEnumCaseNode[]
     */
    public function getCases(): array
    {
        return array_map(
            fn (ReflectionEnumCase|ReflectionEnumUnitCase|ReflectionEnumBackedCase $case) => new PhpEnumCaseNode($case),
            $this->reflection->getCases(),
        );
    }
}
