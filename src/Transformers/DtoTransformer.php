<?php

namespace Spatie\TypescriptTransformer\Transformers;

use ReflectionClass;
use ReflectionProperty;
use Spatie\DataTransferObject\DataTransferObject;
use Spatie\DataTransferObject\DataTransferObjectCollection;
use Spatie\DataTransferObject\FieldValidator;

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
            fn (ReflectionProperty $property) => $this->resolveTypeDefinition($property),
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
            fn (ReflectionProperty $property) => ! $property->isStatic()
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

                if(empty($typeDefinition->allowedArrayTypes)){
                    return true;
                }

                return $type !== 'array'; // Remove array type if there are array types
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
            'array' => 'Array<any>',
        ];

        if (array_key_exists($type, $mapping)) {
            return $mapping[$type];
        }

        return $this->addMissingSymbol($type);
    }
}
