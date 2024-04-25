<?php

namespace Spatie\TypeScriptTransformer\Transformers\EnumProviders;

use BackedEnum;
use ReflectionClass;
use ReflectionEnum;
use UnitEnum;

class PhpEnumProvider implements EnumProvider
{
    public function isEnum(ReflectionClass $reflection): bool
    {
        return $reflection->isEnum();
    }

    public function isValidUnion(ReflectionClass $reflection): bool
    {
        return (new ReflectionEnum($reflection->getName()))->isBacked();
    }

    /**
     * @return array<int, array{name: string, value:string|int|null}>
     */
    public function resolveCases(ReflectionClass $reflection): array
    {
        /** @var class-string<UnitEnum> $enumClass */
        $enumClass = $reflection->getName();

        return array_map(
            fn ($case) => [
                'name' => $case->name,
                'value' => $case instanceof BackedEnum ? $case->value : null,
            ],
            $enumClass::cases()
        );
    }
}
