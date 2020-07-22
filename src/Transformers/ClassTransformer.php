<?php

namespace Spatie\TypescriptTransformer\Transformers;

use ReflectionClass;
use ReflectionProperty;
use Spatie\DataTransferObject\FieldValidator;
use Spatie\TypescriptTransformer\Actions\ResolvePropertyTypesAction;
use Spatie\TypescriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypescriptTransformer\Structures\Type;
use Spatie\TypescriptTransformer\ValueObjects\ClassProperty;

abstract class ClassTransformer implements Transformer
{
    abstract protected function getClassPropertyProcessors(): array;

    public function transform(ReflectionClass $class, string $name): Type
    {
        $missingSymbols = new MissingSymbolsCollection();

        $properties = $this->resolveProperties($class);

        $properties = array_map(
            fn(ReflectionProperty $property) => $this->resolveTypeDefinition($property, $missingSymbols),
            $properties
        );

        $output = "export type {$name} = {" . PHP_EOL;

        foreach ($properties as $property) {
            $output .= "    {$property}" . PHP_EOL;
        }

        $output .= '}' . PHP_EOL;

        return Type::create(
            $class,
            $name,
            $output,
            $missingSymbols
        );
    }

    protected function resolveProperties(ReflectionClass $class)
    {
        $properties = array_filter(
            $class->getProperties(ReflectionProperty::IS_PUBLIC),
            fn(ReflectionProperty $property) => ! $property->isStatic()
        );

        return array_values($properties);
    }

    protected function resolveTypeDefinition(
        ReflectionProperty $reflection,
        MissingSymbolsCollection $missingSymbolsCollection
    ): string {
        $resolvePropertyTypesAction = new ResolvePropertyTypesAction(
            $missingSymbolsCollection,
            $this->getClassPropertyProcessors()
        );

        $types = $resolvePropertyTypesAction->execute(
            $this->resolveClassProperty($reflection)
        );

        return "{$reflection->getName()} : " . implode(' | ', $types) . ';';
    }

    protected function resolveClassProperty(ReflectionProperty $reflection): ClassProperty
    {
        $fieldValidator = FieldValidator::fromReflection($reflection);

        $types = $fieldValidator->isNullable
            ? array_merge($fieldValidator->allowedTypes, ['null'])
            : $fieldValidator->allowedTypes;

        return ClassProperty::create(
            $reflection,
            $types,
            $fieldValidator->allowedArrayTypes
        );
    }
}
