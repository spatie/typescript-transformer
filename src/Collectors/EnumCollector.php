<?php

namespace Spatie\TypeScriptTransformer\Collectors;

use BackedEnum;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\TypeReflectors\ClassTypeReflector;

class EnumCollector extends DefaultCollector
{
    public function getTransformedType(ReflectionClass $class): ?TransformedType
    {
        $transformers = array_map('get_class', $this->config->getTransformers());

        if (! \in_array(EnumTransformer::class, $transformers, true)) {
            return null;
        }

        $reflector = ClassTypeReflector::create($class);

        if (! $reflector->getReflectionClass()->implementsInterface(BackedEnum::class)) {
            return null;
        }

        $transformedType = $reflector->getType()
            ? $this->resolveAlreadyTransformedType($reflector)
            : $this->resolveTypeViaTransformer($reflector);

        if ($reflector->isInline()) {
            $transformedType->name = null;
            $transformedType->isInline = true;
        }

        return $transformedType;
    }
}
