<?php

namespace Spatie\TypeScriptTransformer\Laravel\ClassPropertyProcessors;

use Illuminate\Support\Collection;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use ReflectionProperty;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\Transformers\ClassPropertyProcessors\ClassPropertyProcessor;
use Spatie\TypeScriptTransformer\TypeScript\TypeReference;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptGeneric;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptUnion;
use Spatie\TypeScriptTransformer\Visitor\VisitTypeScriptTreeAction;

class ReplaceLaravelCollectionByArrayClassPropertyProcessor implements ClassPropertyProcessor
{
    public function __construct(
        protected VisitTypeScriptTreeAction $visitTypeScriptTreeAction = new VisitTypeScriptTreeAction(),
    ) {
    }

    public function execute(
        ReflectionProperty $reflection,
        ?TypeNode $annotation,
        TypeScriptProperty $property
    ): ?TypeScriptProperty {
        $this->visitTypeScriptTreeAction->execute($property->type, function (TypeScriptGeneric $generic) {
            $isCollection = $generic->type instanceof TypeReference
                && $generic->type->reference instanceof ClassStringReference
                && is_a($generic->type->reference->classString, Collection::class, true);

            if (! $isCollection) {
                return;
            }

            if (count($generic->genericTypes) !== 2) {
                // Someone messed with the type, let's skip it
                return;
            }

            $isRecord = $generic->genericTypes[0] instanceof TypeScriptUnion || $generic->genericTypes[0] instanceof TypeScriptString;

//            $generic->type = new

            if ($isCollection) {
//                $generic->type = new TypeReference(new TypeScriptArray());
            }
        }, [TypeScriptGeneric::class]);

        return $property;
    }
}
