<?php

namespace Spatie\TypescriptTransformer\Transformers;

use ReflectionClass;
use Spatie\DataTransferObject\DataTransferObjectCollection;
use Spatie\TypescriptTransformer\Structures\MissingSymbolsCollection;

class DtoCollectionTransformer extends InlineTransformer
{
    public function canTransform(ReflectionClass $class): bool
    {
        return is_subclass_of($class->getName(), DataTransferObjectCollection::class);
    }

    protected function transform(ReflectionClass $class, string $name): string
    {
        return "Array<{$this->resolveType($class)}>";
    }

    private function resolveType(ReflectionClass $class): string
    {
        $returnType = $class->getMethod('current')->getReturnType();

        if (empty($returnType)) {
            return 'any';
        }

        $name = $returnType->getName();

        if (! $returnType->isBuiltin()) {
            return $this->addMissingSymbol($name);
        }

        return $returnType->allowsNull()
            ? "{$name} | null"
            : $name;
    }
}
