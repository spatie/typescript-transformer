<?php

namespace Spatie\TypeScriptTransformer\Types;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use Spatie\TypeScriptTransformer\Exceptions\UnableToTransformUsingAttribute;

/** @psalm-immutable */
class StructType implements Type
{
    /** @var array<string, \phpDocumentor\Reflection\Type> */
    private array $types;

    public static function fromArray(array $properties): self
    {
        $resolver = new TypeResolver();

        $types = [];

        foreach ($properties as $name => $property) {
            if (is_string($property)) {
                $types[$name] = $resolver->resolve($property);

                continue;
            }

            if (is_array($property)) {
                $types[$name] = self::fromArray($property);

                continue;
            }

            throw UnableToTransformUsingAttribute::create($property);
        }

        return new self($types);
    }

    public function __construct(array $types)
    {
        $this->types = $types;
    }

    public function __toString(): string
    {
        return 'struct';
    }

    public function getTypes(): array
    {
        return $this->types;
    }
}
