<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use ReflectionClass;
use ReflectionProperty;
use Spatie\StructureDiscoverer\Collections\UsageCollection;
use Spatie\TypeScriptTransformer\Actions\FindClassNameFqcnAction;
use Spatie\TypeScriptTransformer\TypeScript\TypeReference;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptUnion;

class DataClassTransformer extends ClassTransformer
{
    public function shouldTransform(ReflectionClass $reflection): bool
    {
        return $reflection->implementsInterface(\Spatie\LaravelData\Contracts\BaseData::class);
    }

    protected function createProperty(
        ReflectionClass $reflectionClass,
        ReflectionProperty $reflectionProperty,
        array $classAnnotations,
        array $constructorAnnotations,
    ): TypeScriptProperty {
        $property = parent::createProperty(
            $reflectionClass,
            $reflectionProperty,
            $classAnnotations,
            $constructorAnnotations,
        );

        $this->replaceLazy($property);

        return $property;
    }

    protected function replaceLazy(
        TypeScriptProperty $property,
    ): void {
        if (! $property->type instanceof TypeScriptUnion) {
            return;
        }

        for ($i = 0; $i < count($property->type->types); $i++) {
            $subType = $property->type->types[$i];

            if ($subType instanceof TypeReference && is_a($subType->reference, \Spatie\LaravelData\Lazy::class, true)) {
                $property->isOptional = true;

                unset($property->type->types[$i]);
            }
        }

        $property->type->types = array_values($property->type->types);
    }
}
