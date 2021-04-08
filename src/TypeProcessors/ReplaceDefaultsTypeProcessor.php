<?php

namespace Spatie\TypeScriptTransformer\TypeProcessors;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;

class ReplaceDefaultsTypeProcessor implements TypeProcessor
{
    use ProcessesTypes;

    /** @var array<string, Type> */
    private array $mapping;

    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    public function process(
        Type $type,
        ReflectionProperty | ReflectionParameter | ReflectionMethod $reflection,
        MissingSymbolsCollection $missingSymbolsCollection
    ): ?Type {
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
