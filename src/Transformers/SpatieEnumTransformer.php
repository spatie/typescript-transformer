<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use Illuminate\Support\Collection;
use ReflectionClass;
use Spatie\Enum\Enum as SpatieEnum;

class SpatieEnumTransformer extends EnumTransformer
{
    public function canTransform(ReflectionClass $class): bool
    {
        return $class->isSubclassOf(SpatieEnum::class);
    }

    protected function getOptions(ReflectionClass $class): Collection
    {
        return collect($class->getName()::cases())->mapWithKeys(fn(SpatieEnum $case) => [
            $case->label => $case->value,
        ]);
    }
}
