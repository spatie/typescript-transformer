<?php

namespace Spatie\TypeScriptTransformer\TypeProcessors;

use Illuminate\Support\Enumerable;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionUnionType;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\TypeProcessors\TypeProcessor;

class LaravelCollectionTypeProcessor implements TypeProcessor
{
    public function process(
        Type $type,
        ReflectionProperty|ReflectionParameter|ReflectionMethod $reflection,
        MissingSymbolsCollection $missingSymbolsCollection
    ): Type {
        if (! $this->hasLaravelCollection($reflection)) {
            return $type;
        }

        if ($type instanceof Array_) {
            return $type;
        }

        if ($this->isLaravelCollectionObject($type)) {
            return new Array_();
        }

        if ($type instanceof Nullable) {
            return $this->replaceLaravelCollectionInNullable($type);
        }

        if ($type instanceof Compound) {
            return $this->replaceLaravelCollectionInCompound($type);
        }

        return new Compound([$type, new Array_()]);
    }

    private function hasLaravelCollection(ReflectionProperty|ReflectionParameter|ReflectionMethod $reflection): bool
    {
        $type = null;

        if ($reflection instanceof ReflectionProperty || $reflection instanceof ReflectionParameter) {
            $type = $reflection->getType();
        }

        if ($reflection instanceof ReflectionMethod) {
            $type = $reflection->getReturnType();
        }

        if ($type === null) {
            return false;
        }

        $typeNames = $type instanceof ReflectionUnionType
            ? array_map(fn (ReflectionNamedType $type) => $type->getName(), $type->getTypes())
            : [$type->getName()];

        foreach ($typeNames as $typeName) {
            if (is_a($typeName, Enumerable::class, true)) {
                return true;
            }
        }

        return false;
    }

    private function replaceLaravelCollectionInCompound(Compound $compound): Compound
    {
        $types = iterator_to_array($compound->getIterator());

        $arraysInType = array_filter(
            $types,
            function (Type $type) {
                if ($type instanceof Nullable) {
                    return $type->getActualType() instanceof Array_;
                }

                return $type instanceof Array_;
            }
        );

        $types = array_filter(
            $types,
            function (Type $type) {
                if ($type instanceof Nullable) {
                    return ! $this->isLaravelCollectionObject($type->getActualType());
                }

                return ! $this->isLaravelCollectionObject($type);
            }
        );

        return empty($arraysInType)
            ? new Compound(array_merge($types, [new Array_()]))
            : new Compound($types);
    }

    private function replaceLaravelCollectionInNullable(Nullable $nullable): Nullable
    {
        $actualType = $nullable->getActualType();

        if ($this->isLaravelCollectionObject($actualType)) {
            return new Nullable(new Array_());
        }

        if ($actualType instanceof Compound) {
            return new Nullable($this->replaceLaravelCollectionInCompound($actualType));
        }

        if ($actualType instanceof Array_) {
            return $nullable;
        }

        return new Nullable(
            new Compound([$actualType, new Array_()])
        );
    }

    private function isLaravelCollectionObject(Type $type): bool
    {
        if (! $type instanceof Object_) {
            return false;
        }

        return is_a((string) $type->getFqsen(), Enumerable::class, true);
    }
}
