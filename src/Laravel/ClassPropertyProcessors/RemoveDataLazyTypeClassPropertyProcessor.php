<?php

namespace Spatie\TypeScriptTransformer\Laravel\ClassPropertyProcessors;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use ReflectionProperty;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\Transformers\ClassPropertyProcessors\ClassPropertyProcessor;
use Spatie\TypeScriptTransformer\TypeScript\TypeReference;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptUnion;

class RemoveDataLazyTypeClassPropertyProcessor implements ClassPropertyProcessor
{
    protected array $lazyTypes = [
        'Spatie\LaravelData\Lazy',
        'Spatie\LaravelData\Support\Lazy\ClosureLazy',
        'Spatie\LaravelData\Support\Lazy\ConditionalLazy',
        'Spatie\LaravelData\Support\Lazy\DefaultLazy',
        'Spatie\LaravelData\Support\Lazy\InertiaLazy',
        'Spatie\LaravelData\Support\Lazy\RelationalLazy',
    ];

    public function execute(ReflectionProperty $reflection, ?TypeNode $annotation, TypeScriptProperty $property): ?TypeScriptProperty
    {
        if (! $property->type instanceof TypeScriptUnion) {
            return $property;
        }

        for ($i = 0; $i < count($property->type->types); $i++) {
            $subType = $property->type->types[$i];

            if ($subType instanceof TypeReference && $subType->reference instanceof ClassStringReference && in_array($subType->reference->classString, $this->lazyTypes)) {
                $property->isOptional = true;

                unset($property->type->types[$i]);
            }
        }

        $property->type->types = array_values($property->type->types);

        if (count($property->type->types) === 1) {
            $property->type = $property->type->types[0];
        }

        return $property;
    }
}
