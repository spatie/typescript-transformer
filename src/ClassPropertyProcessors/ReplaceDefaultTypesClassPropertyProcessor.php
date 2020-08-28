<?php

namespace Spatie\TypeScriptTransformer\ClassPropertyProcessors;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionProperty;

class ReplaceDefaultTypesClassPropertyProcessor implements ClassPropertyProcessor
{
    use ProcessesClassProperties;

    /** @var array<string, Type> */
    private array $mapping;

    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    public function process(Type $type, ReflectionProperty $reflection): ?Type
    {
        return $this->walk($type, function (Type $type) {
            if (! $type instanceof Object_) {
                return $type;
            }

            foreach ($this->mapping as $replacementClass => $replacementType) {
                if (ltrim((string) $type->getFqsen(), '\\') === $replacementClass) {
                    return $replacementType;
                }
            }

            return $type;
        });
    }
}
