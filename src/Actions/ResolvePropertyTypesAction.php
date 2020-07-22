<?php

namespace Spatie\TypescriptTransformer\Actions;

use Spatie\TypescriptTransformer\ClassPropertyProcessors\ClassPropertyProcessor;
use Spatie\TypescriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypescriptTransformer\ValueObjects\ClassProperty;

class ResolvePropertyTypesAction
{
    private MissingSymbolsCollection $missingSymbolsCollection;

    private array $classPropertyProcessors;

    public function __construct(
        MissingSymbolsCollection $missingSymbolsCollection,
        array $classPropertyProcessors
    ) {
        $this->missingSymbolsCollection = $missingSymbolsCollection;
        $this->classPropertyProcessors = $classPropertyProcessors;
    }

    public function execute(ClassProperty $classProperty): array
    {
        $classProperty = $this->processClassProperty($classProperty);

        $classProperty = $this->removeRedundantTypes($classProperty);

        $types = array_map(function (string $type) {
            return $this->mapType($type);
        }, $classProperty->types);

        $arrayTypes = array_map(function (string $type) {
            return $this->mapType($type);
        }, $classProperty->arrayTypes);

        if (! empty($arrayTypes)) {
            $types[] = 'Array<' . implode(' | ', $arrayTypes) . '>';
        }

        return array_unique($types);
    }

    protected function processClassProperty(ClassProperty $classProperty): ClassProperty
    {
        return array_reduce(
            $this->classPropertyProcessors,
            fn (ClassProperty $property, ClassPropertyProcessor $processor) => $processor->process($property),
            $classProperty
        );
    }

    protected function removeRedundantTypes(ClassProperty $classProperty): ClassProperty
    {
        if (in_array('array', $classProperty->types) && ! empty($classProperty->arrayTypes)) {
            $classProperty->types = array_filter(
                $classProperty->types,
                fn(string $type) => $type !== 'array'
            );
        }

        return $classProperty;
    }

    protected function mapType(string $type): string
    {
        $mapping = [
            'string' => 'string',
            'integer' => 'number',
            'boolean' => 'boolean',
            'double' => 'number',
            'null' => 'null',
            'object' => 'object',
            'array' => 'Array<never>',
            'never' => 'never',
        ];

        if (array_key_exists($type, $mapping)) {
            return $mapping[$type];
        }

        return $this->missingSymbolsCollection->add($type);
    }
}
