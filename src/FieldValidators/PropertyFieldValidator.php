<?php

namespace Spatie\TypescriptTransformer\FieldValidators;

use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;

class PropertyFieldValidator extends FieldValidator
{
    public function __construct(ReflectionProperty $property)
    {
        $this->hasTypeDeclaration = $property->hasType();
        $this->isNullable = $this->resolveAllowsNull($property);
        $this->types = $this->resolveAllowedTypes($property);
        $this->arrayTypes = [];
    }

    private function resolveAllowsNull(ReflectionProperty $property): bool
    {
        if (! $property->getType()) {
            return true;
        }

        return $property->getType()->allowsNull();
    }

    private function resolveAllowedTypes(ReflectionProperty $property): array
    {
        // We cast to array to support future union types in PHP 8
        $types = [$property->getType()];

        return $this->normaliseTypes(...$types);
    }

    private function normaliseTypes(?ReflectionType ...$types): array
    {
        return array_filter(array_map(
            function (?ReflectionType $type) {
                if ($type instanceof ReflectionNamedType) {
                    $type = $type->getName();
                }

                return self::$typeMapping[$type] ?? $type;
            },
            $types
        ));
    }
}
