<?php

namespace Spatie\TypeScriptTransformer\ClassPropertyProcessors;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use ReflectionProperty;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\Transformers\ClassPropertyProcessors\ClassPropertyProcessor;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeReference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptArray;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptGeneric;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptUnion;
use Spatie\TypeScriptTransformer\Visitor\Visitor;
use Spatie\TypeScriptTransformer\Visitor\VisitorOperation;

class FixArrayLikeStructuresClassPropertyProcessor implements ClassPropertyProcessor
{
    protected Visitor $visitor;

    /**
     * @param bool $replaceArrays
     * @param string[] $arrayLikeClassesToReplace
     */
    public function __construct(
        protected bool $replaceArrays = true,
        protected array $arrayLikeClassesToReplace = [],
    ) {
        $this->visitor = Visitor::create()->before(function (TypeScriptGeneric $generic) {
            $isCollection = $generic->type instanceof TypeReference
                && $generic->type->reference instanceof ClassStringReference
                && in_array($generic->type->reference->classString, $this->arrayLikeClassesToReplace);

            $isArrayToReplace = $this->replaceArrays
                && $generic->type instanceof TypeScriptIdentifier
                && $generic->type->name === 'Array'
                && count($generic->genericTypes) === 2; // One type is totally valid

            if (! $isCollection && ! $isArrayToReplace) {
                return VisitorOperation::keep();
            }

            $genericTypesCount = count($generic->genericTypes);

            if ($genericTypesCount > 2 || $genericTypesCount === 0) {
                // Someone messed with the type, let's skip it
                return VisitorOperation::keep();
            }

            if ($genericTypesCount === 1) {
                return VisitorOperation::replace(new TypeScriptArray([$generic->genericTypes[0]]));
            }

            $isRecord = $generic->genericTypes[0] instanceof TypeScriptUnion || $generic->genericTypes[0] instanceof TypeScriptString;

            if ($isRecord) {
                return VisitorOperation::replace(new TypeScriptGeneric(
                    new TypeScriptIdentifier('Record'),
                    [
                        $generic->genericTypes[0],
                        $generic->genericTypes[1],
                    ]
                ));
            }

            return VisitorOperation::replace(new TypeScriptArray([$generic->genericTypes[1]]));
        }, [TypeScriptGeneric::class]);
    }

    public function replaceArrayLikeClass(string ...$class): self
    {
        array_push($this->arrayLikeClassesToReplace, ...$class);

        return $this;
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
