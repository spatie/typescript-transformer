<?php

namespace Spatie\TypeScriptTransformer\Laravel\Transformers;

use Spatie\TypeScriptTransformer\Laravel\ClassPropertyProcessors\ReplaceLaravelCollectionByArrayClassPropertyProcessor;
use Spatie\TypeScriptTransformer\Transformers\ClassTransformer;

abstract class LaravelClassTransformer extends ClassTransformer
{
    protected function classPropertyProcessors(): array
    {
        return array_merge(parent::classPropertyProcessors(), [
            new ReplaceLaravelCollectionByArrayClassPropertyProcessor(),
        ]);
    }
}
