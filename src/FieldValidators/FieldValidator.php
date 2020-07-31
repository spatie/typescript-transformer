<?php

namespace Spatie\TypescriptTransformer\FieldValidators;

use ReflectionProperty;

abstract class FieldValidator
{
    public bool $isNullable;

    public array $types;

    public array $arrayTypes;

    protected static array $typeMapping = [
        'int' => 'integer',
        'bool' => 'boolean',
        'float' => 'double',
    ];

    protected bool $hasTypeDeclaration;

    public static function fromReflection(ReflectionProperty $property): FieldValidator
    {
        $docDefinition = null;

        if ($property->getDocComment()) {
            preg_match(
                DocblockFieldValidator::DOCBLOCK_REGEX,
                $property->getDocComment(),
                $matches
            );

            $docDefinition = $matches[0] ?? null;
        }

        if ($docDefinition) {
            return new DocblockFieldValidator($docDefinition, $property->isDefault());
        }

        return new PropertyFieldValidator($property);
    }
}
