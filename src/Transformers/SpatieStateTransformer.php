<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use ReflectionClass;
use Spatie\ModelStates\State;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\Transformers\Transformer;

class SpatieStateTransformer implements Transformer
{
    public function transform(ReflectionClass $class, string $name): ?TransformedType
    {
        if (! $this->isTransformable($class)) {
            return null;
        }

        return TransformedType::create(
            $class,
            $name,
            $this->resolveOptions($class),
        );
    }

    private function isTransformable(ReflectionClass $class): bool
    {
        $parent = $class->getParentClass();

        if (empty($parent)) {
            return false;
        }

        return $parent->getName() === State::class;
    }

    private function resolveOptions(ReflectionClass $class): string
    {
        /** @var \Spatie\ModelStates\State $state */
        $state = $class->getName();

        $states = array_filter(
            $state::all()->toArray(),
            fn (string $stateClass) => $stateClass !== $state
        );

        $options = array_map(
            fn (string $stateClass) => "'{$stateClass::getMorphClass()}'",
            $states
        );

        return implode(' | ', $options);
    }
}
