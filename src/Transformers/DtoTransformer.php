<?php

namespace Spatie\TypescriptTransformer\Transformers;

use ReflectionClass;
use ReflectionProperty;
use Spatie\DataTransferObject\DataTransferObject;
use Spatie\DataTransferObject\FieldValidator;
use Spatie\TypescriptTransformer\Actions\ResolvePropertyTypesAction;
use Spatie\TypescriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypescriptTransformer\Structures\Type;

class DtoTransformer implements Transformer
{
    public function canTransform(ReflectionClass $class): bool
    {
        return is_subclass_of($class->getName(), DataTransferObject::class);
    }

    public function transform(ReflectionClass $class, string $name): Type
    {
        $missingSymbols = new MissingSymbolsCollection();

        $properties = $this->resolveProperties($class);

        $properties = array_map(
            fn (ReflectionProperty $property) => $this->resolveTypeDefinition($property, $missingSymbols),
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

    private function resolveProperties(ReflectionClass $class)
    {
        $properties = array_filter(
            $class->getProperties(ReflectionProperty::IS_PUBLIC),
            fn (ReflectionProperty $property) => ! $property->isStatic()
        );

        return array_values($properties);
    }

    protected function resolveTypeDefinition(
        ReflectionProperty $property,
        MissingSymbolsCollection $missingSymbolsCollection
    ): string {
        $fieldValidator = FieldValidator::fromReflection($property);

        $resolvePropertyTypesAction = new ResolvePropertyTypesAction(
            $missingSymbolsCollection
        );

        $types = $resolvePropertyTypesAction->execute(
            $fieldValidator->allowedTypes,
            $fieldValidator->allowedArrayTypes,
            $fieldValidator->isNullable
        );

        return "{$property->getName()} : " . implode(' | ', $types) . ';';
    }
}
