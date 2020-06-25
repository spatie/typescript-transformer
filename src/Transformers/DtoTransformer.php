<?php

namespace Spatie\TypescriptTransformer\Transformers;

use ReflectionClass;
use ReflectionProperty;
use Spatie\DataTransferObject\DataTransferObject;
use Spatie\DataTransferObject\FieldValidator;
use Spatie\TypescriptTransformer\Actions\ResolvePropertyTypesAction;
use Spatie\TypescriptTransformer\Structures\TypesCollection;

class DtoTransformer extends Transformer
{
    public function canTransform(ReflectionClass $class): bool
    {
        return is_subclass_of($class->getName(), DataTransferObject::class);
    }

    protected function transform(ReflectionClass $class, string $name): string
    {
        $properties = $this->resolveProperties($class);

        $properties = array_map(
            fn(ReflectionProperty $property) => $this->resolveTypeDefinition($property),
            $properties
        );

        $output = "export type {$name} = {" . PHP_EOL;

        foreach ($properties as $property) {
            $output .= "    {$property}" . PHP_EOL;
        }

        $output .= '}' . PHP_EOL;

        return $output;
    }

    private function resolveProperties(ReflectionClass $class)
    {
        $properties = array_filter(
            $class->getProperties(ReflectionProperty::IS_PUBLIC),
            fn(ReflectionProperty $property) => ! $property->isStatic()
        );

        return array_values($properties);
    }

    private function resolveTypeDefinition(
        ReflectionProperty $property
    ): string {
        $fieldValidator = FieldValidator::fromReflection($property);

        $resolvePropertyTypesAction = new ResolvePropertyTypesAction(
            $this
        );

        $types = $resolvePropertyTypesAction->execute(
            $fieldValidator->allowedTypes,
            $fieldValidator->allowedArrayTypes,
            $fieldValidator->isNullable
        );

        return "{$property->getName()} : " . implode(' | ', $types) . ';';
    }
}
