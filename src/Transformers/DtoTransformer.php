<?php

namespace Spatie\TypescriptTransformer\Transformers;

use ReflectionClass;
use ReflectionProperty;
use Spatie\DataTransferObject\DataTransferObject;
use Spatie\DataTransferObject\DataTransferObjectCollection;
use Spatie\DataTransferObject\FieldValidator;
use Spatie\TypescriptTransformer\Structures\TransformedType;

class DtoTransformer extends Transformer
{
    public function canTransform(ReflectionClass $class): bool
    {
        return is_subclass_of($class->getName(), DataTransferObject::class);
    }

    public function transform(ReflectionClass $class, string $name): string
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
        $typeDefinition = FieldValidator::fromReflection($property);

        $types = array_filter(
            $typeDefinition->allowedTypes,
            function (string $type) use ($typeDefinition) {
                if (str_ends_with($type, '[]')) {
                    return false;
                }

                if ($this->isCollectionType($type)) {
                    return false;
                }

                return empty($typeDefinition->allowedArrayTypes)
                    ? true
                    : $type !== 'array';
            }
        );

        $types = array_map(function (string $type) {
            return $this->mapType($type);
        }, $types);

        $arrayTypes = array_map(function (string $type) {
            return $this->mapType($type);
        }, $typeDefinition->allowedArrayTypes);

        if (count($arrayTypes) > 0) {
            $types[] = 'Array<' . implode(' | ', $arrayTypes) . '>';
        }

        if (count($types) === 0) {
            $types[] = 'any';
        }

        if ($typeDefinition->isNullable) {
            $types[] = 'null';
        }

        return "{$property->getName()} : " . implode(' | ', $types) . ';';
    }

    private function mapType(string $type): string
    {
        $mapping = [
            'string' => 'string',
            'integer' => 'number',
            'boolean' => 'boolean',
            'double' => 'number',
            'null' => 'null',
            'object' => 'object',
            'array' => 'Array<>',
        ];

        if (array_key_exists($type, $mapping)) {
            return $mapping[$type];
        }

        if (! in_array($type, $this->missingSymbols)) {
            $this->missingSymbols[] = $type;
        }

        return "{%{$type}%}";
    }

    private function resolveArrayTypes(
        FieldValidator $typeDefinition
    ): array {
        $collectionTypes = array_filter(
            $typeDefinition->allowedTypes,
            fn(string $type) => $this->isCollectionType($type)
        );

        $types = $typeDefinition->allowedTypes;

        foreach ($collectionTypes as $type){
            $types = array_merge(
                $this->getTypesInCollection($type),
                $types
            );
        }

        return $types;
    }

    protected function isCollectionType(string $type): bool
    {
        return is_subclass_of($type, DataTransferObjectCollection::class);
    }

    protected function getTypesInCollection(string $type): array
    {
        $reflection = (new ReflectionClass($type))->getMethod('current');

        if(!$reflection->hasReturnType()){
            return [];
        }

        return [$reflection->getReturnType()->getName()];
    }
}
