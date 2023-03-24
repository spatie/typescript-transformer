<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use BackedEnum;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionEnum;
use ReflectionEnumBackedCase;

class NativeEnumTransformer extends EnumTransformer
{
    public function canTransform(ReflectionClass $class): bool
    {
        return $class->isEnum() && $class->implementsInterface(BackedEnum::class);
    }

    protected function getOptions(ReflectionClass $class): Collection
    {
        $enum = (new ReflectionEnum($class->getName()));

        return collect($enum->getCases())->mapWithKeys(fn (ReflectionEnumBackedCase $case) => [
            $case->getName() => $case->getBackingValue(),
        ]);
    }
}
