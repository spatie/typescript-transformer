<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use MyCLabs\Enum\Enum;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Structures\TransformedType;

class MyclabsEnumTransformer implements Transformer
{
    public function canTransform(ReflectionClass $class): bool
    {
        return $class->isSubclassOf(Enum::class);
    }

    public function transform(ReflectionClass $class, string $name): TransformedType
    {
        return TransformedType::create(
            $class,
            $name,
            "export type {$name} = {$this->resolveOptions($class)};"
        );
    }

    protected function resolveOptions(ReflectionClass $class): string
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
