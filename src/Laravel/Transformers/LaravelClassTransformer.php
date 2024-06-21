<?php

namespace Spatie\TypeScriptTransformer\Laravel\Transformers;

use Spatie\TypeScriptTransformer\ClassPropertyProcessors\FixArrayLikeStructuresClassPropertyProcessor;
use Spatie\TypeScriptTransformer\Transformers\ClassTransformer;

abstract class LaravelClassTransformer extends ClassTransformer
{
    protected function classPropertyProcessors(): array
    {
        $processors = parent::classPropertyProcessors();

        foreach ($processors as $processor) {
            if ($processor instanceof FixArrayLikeStructuresClassPropertyProcessor) {
                $processor->replaceArrayLikeClass(
                    \Illuminate\Support\Collection::class,
                    \Illuminate\Database\Eloquent\Collection::class,
                );
            }
        }

        return $processors;
    }
}
