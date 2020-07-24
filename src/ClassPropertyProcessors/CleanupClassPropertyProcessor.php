<?php

namespace Spatie\TypescriptTransformer\ClassPropertyProcessors;

use Spatie\TypescriptTransformer\ValueObjects\ClassProperty;

class CleanupClassPropertyProcessor implements ClassPropertyProcessor
{
    public function process(ClassProperty $classProperty): ClassProperty
    {
        $classProperty->types = array_values(array_filter(
            $classProperty->types,
            fn (string $type) => ! $this->isInvalidType($type)
        ));

        return $classProperty;
    }

    protected function isInvalidType(string $type): bool
    {
        return strlen($type) >= 2 && substr($type, -2, 2) === '[]';
    }
}
