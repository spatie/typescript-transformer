<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use Illuminate\Support\Collection;
use ReflectionClass;
use Spatie\ModelStates\State;

class SpatieStateTransformer extends EnumTransformer
{
    public function canTransform(ReflectionClass $class): bool
    {
        if ($parentClass = $class->getParentClass()) {
            return $parentClass->getName() === State::class;
        }

        return false;
    }

    protected function getOptions(ReflectionClass $class): Collection
    {
        return collect($class->getName()::all())
            ->filter(fn(string $case) => $case !== $class->getName())
            ->mapWithKeys(fn(string $case) => [
                $case::getMorphClass() => $case::getMorphClass(),
            ]);
    }
}
