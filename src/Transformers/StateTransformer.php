<?php

namespace Spatie\TypescriptTransformer\Transformers;

use ReflectionClass;
use Spatie\ModelStates\State;
use Spatie\TypescriptTransformer\Type;

class StateTransformer implements Transformer
{
    public function canTransform(ReflectionClass $class): bool
    {
        $parent = $class->getParentClass();

        if (empty($parent)) {
            return false;
        }

        return $parent->getName() === State::class;
    }

    public function transform(ReflectionClass $class, string $name): string
    {
        return "export type {$name} = {$this->resolveOptions($class)};";
    }

    private function resolveOptions(ReflectionClass $class): string
    {
        /** @var \Spatie\ModelStates\State $state */
        $state = $class->getName();

        $options = array_map(
            fn(string $stateClass) => "'{$stateClass::getMorphClass()}'",
            $state::all()->toArray()
        );

        return implode(' | ', $options);
    }
}
