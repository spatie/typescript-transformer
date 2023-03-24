<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use Illuminate\Support\Collection;
use MyCLabs\Enum\Enum as MyclabsEnum;
use ReflectionClass;

class MyclabsEnumTransformer extends EnumTransformer
{
    public function canTransform(ReflectionClass $class): bool
    {
        return $class->isSubclassOf(MyclabsEnum::class);
    }

    protected function getOptions(ReflectionClass $class): Collection
    {
        return collect($class->getName()::values())->mapWithKeys(fn (MyclabsEnum $case) => [
            $case->getKey() => $case->getValue(),
        ]);
    }
}
