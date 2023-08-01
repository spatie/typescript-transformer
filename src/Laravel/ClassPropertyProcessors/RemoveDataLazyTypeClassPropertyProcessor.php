<?php

namespace Spatie\TypeScriptTransformer\Laravel\ClassPropertyProcessors;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use ReflectionProperty;
use Spatie\TypeScriptTransformer\Transformers\ClassPropertyProcessors\ClassPropertyProcessor;
use Spatie\TypeScriptTransformer\TypeScript\TypeReference;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptUnion;

class RemoveDataLazyTypeClassPropertyProcessor implements ClassPropertyProcessor
{
    public function execute(ReflectionProperty $reflection, ?TypeNode $annotation, TypeScriptProperty $property): ?TypeScriptProperty
    {
        if (! $property->type instanceof TypeScriptUnion) {
            return $property;
        }

        for ($i = 0; $i < count($property->type->types); $i++) {
            $subType = $property->type->types[$i];

            if ($subType instanceof TypeReference && is_a($subType->reference, \Spatie\LaravelData\Lazy::class, true)) {
                $property->isOptional = true;

                unset($property->type->types[$i]);
            }
        }

        $property->type->types = array_values($property->type->types);

        return $property;
    }
}
