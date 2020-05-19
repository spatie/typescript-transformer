<?php

namespace Spatie\TypescriptTransformer\Mappers;

use ReflectionClass;
use Spatie\ModelStates\State;

class StateMapper implements Mapper
{
    public function isValid(ReflectionClass $class): bool
    {
        $parent = $class->getParentClass();

        if (empty($parent)) {
            return false;
        }

        return $parent->getName() === State::class;
    }

    public function map(ReflectionClass $class): array
    {
        /** @var \Spatie\ModelStates\State $state */
        $state = $class->getName();

        return array_map(
            fn (string $stateClass) => $stateClass::getMorphClass(),
            $state::all()->toArray()
        );
    }
}
