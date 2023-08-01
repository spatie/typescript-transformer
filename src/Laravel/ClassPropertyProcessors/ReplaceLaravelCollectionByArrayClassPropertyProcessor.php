<?php

namespace Spatie\TypeScriptTransformer\Laravel\ClassPropertyProcessors;

use Illuminate\Support\Collection;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use ReflectionProperty;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\Transformers\ClassPropertyProcessors\ClassPropertyProcessor;
use Spatie\TypeScriptTransformer\TypeScript\TypeReference;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptArray;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptGeneric;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptUnion;
use Spatie\TypeScriptTransformer\Visitor\Visitor;
use Spatie\TypeScriptTransformer\Visitor\VisitorOperation;
use Spatie\TypeScriptTransformer\Visitor\VisitTypeScriptTreeAction;

class ReplaceLaravelCollectionByArrayClassPropertyProcessor implements ClassPropertyProcessor
{
    protected Visitor $visitor;

    public function __construct()
    {
        $this->visitor = Visitor::create()->before(function (TypeScriptGeneric $generic) {
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

            if($isRecord){
                return VisitorOperation::replace(new TypeScriptGeneric(
                    new TypeScriptIdentifier('Record'),
                    [
                        $generic->genericTypes[0],
                        $generic->genericTypes[1]
                    ]
                ));
            }

            return VisitorOperation::replace(new TypeScriptArray([$generic->genericTypes[1]]));
        }, [TypeScriptGeneric::class]);
    }

    public function execute(
        ReflectionProperty $reflection,
        ?TypeNode $annotation,
        TypeScriptProperty $property
    ): ?TypeScriptProperty {
        $property->type = $this->visitor->execute($property->type);

        return $property;
    }
}
