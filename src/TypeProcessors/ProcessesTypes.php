<?php

namespace Spatie\TypeScriptTransformer\TypeProcessors;

use Closure;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\AbstractList;
use phpDocumentor\Reflection\Types\Collection;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\String_;

trait ProcessesTypes
{
    protected function walk(Type $type, Closure $closure): ?Type
    {
        if ($type instanceof Compound) {
            $walkedTypes = array_map(
                fn (Type $type) => $this->walk($type, $closure),
                iterator_to_array($type->getIterator())
            );

            $walkedTypes = array_filter($walkedTypes);

            if (empty($walkedTypes)) {
                return null;
            }

            if (count($walkedTypes) === 1) {
                return current($walkedTypes);
            }

            return $closure(new Compound($walkedTypes));
        }

        if ($type instanceof AbstractList) {
            $walkedValueType = $this->walk($type->getValueType(), $closure);
            $walkedKeyType = $this->walk($type->getKeyType(), $closure);

            if ($walkedValueType === null || $walkedKeyType === null) {
                return null;
            }

            return $closure(
                $this->updateListType($type, $walkedValueType, $walkedKeyType)
            );
        }

        if ($type instanceof Nullable) {
            $walkedType = $this->walk($type->getActualType(), $closure);

            if ($walkedType === null) {
                return null;
            }

            return $closure(new Nullable($walkedType));
        }

        return $closure($type);
    }

    protected function updateListType(
        AbstractList $type,
        Type $valueType,
        ?Type $keyType = null
    ): Type {
        $keyType = $type->getKeyType();

        if ((string) $keyType === (string) new Compound([new String_(), new Integer()])) {
            $keyType = null;
        }

        if ($type instanceof Collection) {
            return new Collection($type->getFqsen(), $valueType, $keyType);
        }

        $typeClass = get_class($type);

        return new $typeClass($valueType, $keyType);
    }
}
