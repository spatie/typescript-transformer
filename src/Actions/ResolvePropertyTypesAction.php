<?php

namespace Spatie\TypescriptTransformer\Actions;

use Spatie\TypescriptTransformer\Structures\MissingSymbolsCollection;

class ResolvePropertyTypesAction
{
    private MissingSymbolsCollection $missingSymbolsCollection;

    public function __construct(MissingSymbolsCollection $missingSymbolsCollection)
    {
        $this->missingSymbolsCollection = $missingSymbolsCollection;
    }

    public function execute(
        array $allowedTypes,
        array $allowedArrayTypes,
        bool $isNullable
    ): array {
        $types = $this->resolveTypes($allowedTypes, $allowedArrayTypes);

        $types = $this->mapTypes($types);
        $arrayTypes = $this->mapArrayTypes($allowedArrayTypes);

        if (count($arrayTypes) > 0) {
            $types[] = 'Array<' . implode(' | ', $arrayTypes) . '>';
        }

        if (count($types) === 0) {
            return ['any'];
        }

        if ($isNullable) {
            $types[] = 'null';
        }

        return array_unique($types);
    }

    protected function resolveTypes(
        array $allowedTypes,
        array $allowedArrayTypes
    ): array {
        return array_filter(
            $allowedTypes,
            function (string $type) use ($allowedArrayTypes) {
                if (str_ends_with($type, '[]')) {
                    return false;
                }

                if (empty($allowedArrayTypes)) {
                    return true;
                }

                if ($type === 'array') {
                    return false;
                }

                return true;
            }
        );
    }

    protected function mapTypes(array $types): array
    {
        return array_map(function (string $type) {
            return $this->mapType($type);
        }, $types);
    }

    protected function mapArrayTypes(array $allowedArrayTypes): array
    {
        return array_map(function (string $type) {
            return $this->mapType($type);
        }, $allowedArrayTypes);
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
            'array' => 'Array<any>',
        ];

        if (array_key_exists($type, $mapping)) {
            return $mapping[$type];
        }

        return $this->missingSymbolsCollection->add($type);
    }
}
