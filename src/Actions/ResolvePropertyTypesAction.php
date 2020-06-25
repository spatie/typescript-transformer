<?php

namespace Spatie\TypescriptTransformer\Actions;

use Spatie\DataTransferObject\FieldValidator;
use Spatie\TypescriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypescriptTransformer\Structures\TypesCollection;
use Spatie\TypescriptTransformer\Transformers\DtoTransformer;
use Spatie\TypescriptTransformer\Transformers\Transformer;

class ResolvePropertyTypesAction
{
    private Transformer $transformer;

    public function __construct(Transformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function execute(
        array $allowedTypes,
        array $allowedArrayTypes,
        bool $isNullable
    ): array {
        $types = array_filter(
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

        $types = array_map(function (string $type) {
            return $this->mapType($type);
        }, $types);

        $arrayTypes = array_map(function (string $type) {
            return $this->mapType($type);
        }, $allowedArrayTypes);

        if (count($arrayTypes) > 0) {
            $types[] = 'Array<' . implode(' | ', $arrayTypes) . '>';
        }

        if (count($types) === 0) {
            $types[] = 'any';
        }

        if ($isNullable) {
            $types[] = 'null';
        }

        return $types;
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

        return $this->transformer->addMissingSymbol($type);
    }
}
