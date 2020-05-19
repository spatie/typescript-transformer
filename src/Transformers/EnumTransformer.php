<?php

namespace Spatie\TypescriptTransformer\Transformers;

use ReflectionClass;
use Spatie\Enum\Enum;

class EnumTransformer implements Transformer
{
    public function canTransform(ReflectionClass $class): bool
    {
        return $class->isSubclassOf(Enum::class);
    }

    public function transform(ReflectionClass $class, string $name): string
    {
        return "export type {$name} = {$this->resolveOptions($class)};";
    }

    private function resolveOptions(ReflectionClass $class): string
    {
        /** @var \Spatie\Enum\Enum $enum */
        $enum = $class->getName();

        $options = array_map(
            fn(Enum $enum) => "'{$enum->getValue()}'",
            $enum::getAll()
        );

        return implode(' | ', $options);
    }
}
