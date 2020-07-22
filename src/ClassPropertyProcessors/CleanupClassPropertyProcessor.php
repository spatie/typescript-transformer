<?php

namespace Spatie\TypescriptTransformer\ClassPropertyProcessors;

use Spatie\TypescriptTransformer\ValueObjects\ClassProperty;

class CleanupClassPropertyProcessor implements ClassPropertyProcessor
{
    public function process(ClassProperty $classProperty): ClassProperty
    {
        return ClassProperty::create(
            $classProperty->property,
            $this->resolveTypes($classProperty),
            $classProperty->arrayTypes
        );
    }

    private function resolveTypes(ClassProperty $classProperty): array
    {
        return array_filter($classProperty->types, function (string $type) use ($classProperty) {
            if (str_ends_with($type, '[]')) {
                return false;
            }

            if ($type === 'array' && ! empty($classProperty->arrayTypes)) {
                return false;
            }

            return true;
        });
    }
}
