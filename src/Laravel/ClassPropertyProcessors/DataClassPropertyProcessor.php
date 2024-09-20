<?php

namespace Spatie\TypeScriptTransformer\Laravel\ClassPropertyProcessors;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Spatie\LaravelData\Attributes\Hidden as DataHidden;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\TypeScriptTransformer\Attributes\Hidden;
use Spatie\TypeScriptTransformer\PhpNodes\PhpPropertyNode;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\Transformers\ClassPropertyProcessors\ClassPropertyProcessor;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeReference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptUnion;

class DataClassPropertyProcessor implements ClassPropertyProcessor
{
    protected array $lazyTypes = [
        'Spatie\LaravelData\Lazy',
        'Spatie\LaravelData\Support\Lazy\ClosureLazy',
        'Spatie\LaravelData\Support\Lazy\ConditionalLazy',
        'Spatie\LaravelData\Support\Lazy\DefaultLazy',
        'Spatie\LaravelData\Support\Lazy\InertiaLazy',
        'Spatie\LaravelData\Support\Lazy\RelationalLazy',
    ];

    public function __construct(
        protected DataConfig $dataConfig,
        protected array $customLazyTypes = [],
    ) {
        $this->lazyTypes = array_merge($this->lazyTypes, $this->customLazyTypes);
    }

    public function execute(
        PhpPropertyNode $phpPropertyNode,
        ?TypeNode $annotation,
        TypeScriptProperty $property
    ): ?TypeScriptProperty {
        if (! empty($phpPropertyNode->getAttributes(Hidden::class)) && ! empty($phpPropertyNode->getAttributes(DataHidden::class))) {
            return null;
        }

        // TODO: somehow get mapping working here without dataconfig and dataproperty
        //        $phpAttributeNodes = $phpPropertyNode->getAttributes(MapOutputName::class);
        //
        //        if ($phpAttributeNodes) {
        //            $property->name = new TypeScriptIdentifier($dataProperty->outputMappedName);
        //        }

        if (! $property->type instanceof TypeScriptUnion) {
            return $property;
        }

        for ($i = 0; $i < count($property->type->types); $i++) {
            $subType = $property->type->types[$i];

            if ($subType instanceof TypeReference && $this->shouldHideReference($subType)) {
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

    protected function shouldHideReference(
        TypeReference $reference
    ): bool {
        if (! $reference->reference instanceof ClassStringReference) {
            return false;
        }

        return in_array($reference->reference->classString, $this->lazyTypes)
            || $reference->reference->classString === Optional::class;
    }
}
