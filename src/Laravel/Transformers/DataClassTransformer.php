<?php

namespace Spatie\TypeScriptTransformer\Laravel\Transformers;

use ReflectionClass;
use Spatie\TypeScriptTransformer\Laravel\ClassPropertyProcessors\RemoveDataLazyTypeClassPropertyProcessor;

class DataClassTransformer extends LaravelClassTransformer
{
    protected function shouldTransform(ReflectionClass $reflection): bool
    {
        return $reflection->implementsInterface(\Spatie\LaravelData\Contracts\BaseData::class);
    }

    protected function classPropertyProcessors(): array
    {
        return array_merge(parent::classPropertyProcessors(), [
            new RemoveDataLazyTypeClassPropertyProcessor(),
        ]);
    }
}
