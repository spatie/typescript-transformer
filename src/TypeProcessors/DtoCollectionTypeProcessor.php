<?php

namespace Spatie\TypeScriptTransformer\TypeProcessors;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Spatie\DataTransferObject\DataTransferObjectCollection;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\TypeReflectors\TypeReflector;

class DtoCollectionTypeProcessor implements TypeProcessor
{
    use ProcessesTypes;

    public function process(
        Type $type,
        ReflectionProperty | ReflectionParameter | ReflectionMethod $reflection,
        MissingSymbolsCollection $missingSymbolsCollection
    ): ?Type {
        return $this->walk($type, function (Type $type) {
            if (! $type instanceof Object_) {
                return $type;
            }

            $fqs = ltrim((string) $type->getFqsen(), '\\');

            if (! is_subclass_of($fqs, DataTransferObjectCollection::class)) {
                return $type;
            }

            $reflection = new ReflectionClass($fqs);

            return new Array_(
                TypeReflector::new($reflection->getMethod('current'))->reflect()
            );
        });
    }
}
