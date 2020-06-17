<?php

namespace Spatie\TypescriptTransformer\Transformers;

use MyCLabs\Enum\Enum;
use ReflectionClass;
use Spatie\TypescriptTransformer\Structures\TransformedType;

class MyclabsEnumTransformer extends Transformer
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
        /** @var \MyCLabs\Enum\Enum $enum */
        $enum = $class->getName();

        $options = array_map(
            fn (Enum $enum) => "'{$enum->getValue()}'",
            $enum::values()
        );

        return implode(' | ', $options);
    }
}
