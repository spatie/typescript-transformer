<?php

namespace Spatie\TypescriptTransformer\Mappers;

use ReflectionClass;
use Spatie\Enum\Enum;

class EnumMapper implements Mapper
{
    public function isValid(ReflectionClass $class): bool
    {
        return $class->isSubclassOf(Enum::class);
    }

    public function map(ReflectionClass $class): array
    {
        /** @var \Spatie\Enum\Enum $enum */
        $enum = $class->getName();

        return array_map(
            fn (Enum $enum) => $enum->getValue(),
            $enum::getAll()
        );
    }
}
