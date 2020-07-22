<?php

namespace Spatie\TypescriptTransformer\ClassPropertyProcessors;

use Spatie\TypescriptTransformer\ValueObjects\ClassProperty;

class CleanupClassPropertyProcessor implements ClassPropertyProcessor
{
    public function process(ClassProperty $classProperty): ClassProperty
    {
        $classProperty->types = array_filter(
            $classProperty->types,
            fn (string $type) => ! str_ends_with($type, '[]')
        );

        return $classProperty;
    }
}
