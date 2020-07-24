<?php

namespace Spatie\TypescriptTransformer\Transformers;

use ReflectionClass;
use Spatie\DataTransferObject\DataTransferObjectCollection;
use Spatie\TypescriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypescriptTransformer\Structures\Type;

class DtoCollectionTransformer implements Transformer
{
    public function canTransform(ReflectionClass $class): bool
    {
        return is_subclass_of($class->getName(), DataTransferObjectCollection::class);
    }

    public function transform(ReflectionClass $class, string $name): Type
    {
        $missingSymbolsCollection = new MissingSymbolsCollection();

        $transformed = "Array<{$this->resolveType($class, $missingSymbolsCollection)}>";

        return Type::createInline($class, $transformed, $missingSymbolsCollection);
    }

    protected function resolveType(
        ReflectionClass $class,
        MissingSymbolsCollection $missingSymbolsCollection
    ): string {
        $returnType = $class->getMethod('current')->getReturnType();

        if (empty($returnType)) {
            return 'never';
        }

        $name = $returnType->getName();

        if (! $returnType->isBuiltin()) {
            return $missingSymbolsCollection->add($name);
        }

        return $returnType->allowsNull()
            ? "{$name} | null"
            : $name;
    }
}
