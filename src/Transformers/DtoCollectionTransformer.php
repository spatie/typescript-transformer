<?php

namespace Spatie\TypescriptTransformer\Transformers;

use ReflectionClass;
use Spatie\DataTransferObject\DataTransferObject;
use Spatie\DataTransferObject\DataTransferObjectCollection;
use Spatie\TypescriptTransformer\Structures\TransformedType;

class DtoCollectionTransformer extends Transformer
{
    public function canTransform(ReflectionClass $class): bool
    {
        return is_subclass_of($class->getName(), DataTransferObjectCollection::class);
    }

    public function transform(ReflectionClass $class, string $name): string
    {
        $output = "export type {$name} = {" . PHP_EOL;

        $output .= "    collection : Array<{$this->resolveType($class)}>;" . PHP_EOL;

        $output .= '}' . PHP_EOL;

        return $output;
    }

    private function resolveType(ReflectionClass $class): string
    {
        $returnType = $class->getMethod('current')->getReturnType();

        if (empty($returnType)) {
            return 'any';
        }

        $name = $returnType->getName();

        if(! $returnType->isBuiltin()){
            $this->missingSymbols[] = $name;

            return "{%{$name}%}";
        }

        return $returnType->allowsNull()
            ? "{$name} | null"
            : $name;
    }
}
