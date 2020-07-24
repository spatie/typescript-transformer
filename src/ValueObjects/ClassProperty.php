<?php

namespace Spatie\TypescriptTransformer\ValueObjects;

use ReflectionProperty;

class ClassProperty
{
    public ReflectionProperty $reflection;

    /** @var string[] */
    public array $types;

    /** @var string[] */
    public array $arrayTypes;

    public static function create(
        ReflectionProperty $property,
        array $types,
        array $arrayTypes
    ): self {
        return new self($property, $types, $arrayTypes);
    }

    public function __construct(
        ReflectionProperty $property,
        array $types,
        array $arrayTypes
    ) {
        $this->reflection = $property;
        $this->types = $types;
        $this->arrayTypes = $arrayTypes;
    }
}
