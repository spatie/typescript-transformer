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
        if (! $this->shouldCollect($class)) {
            return null;
        }

        $reflector = ClassTypeReflector::create($class);

        $transformedType = $reflector->getType()
            ? $this->resolveAlreadyTransformedType($reflector)
            : $this->resolveTypeViaTransformer($reflector);

        if ($reflector->isInline()) {
            $transformedType->name = null;
            $transformedType->isInline = true;
        }

        return $transformedType;
    }

    protected function shouldCollect(ReflectionClass $class): bool
    {
        $transformers = array_map('get_class', $this->config->getTransformers());

        $hasEnumTransformer = \count(
            array_filter($transformers, function (string $transformer) {
                if ($transformer === EnumTransformer::class) {
                    return true;
                }

                return is_subclass_of($transformer, EnumTransformer::class);
            }),
        ) > 0;

        if (! $hasEnumTransformer) {
            return false;
        }

        if (! $class->implementsInterface(BackedEnum::class)) {
            return false;
        }

        return true;
    }
}
