<?php

namespace Spatie\TypeScriptTransformer\Actions;

use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\TypeScript\TypeReference;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptAny;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptArray;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptBoolean;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptIntersection;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNull;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNumber;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptUndefined;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptUnion;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptUnknown;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptVoid;

class TranspileReflectionTypeToTypeScriptNodeAction
{
    public function execute(
        ReflectionType $reflectionType,
        ReflectionClass $reflectionClass,
    ): TypeScriptNode {
        $type = $this->resolveType($reflectionType, $reflectionClass);

        if (
            ! $reflectionType->allowsNull()
            || $type instanceof TypeScriptAny
            || $type instanceof TypeScriptNull) {
            return $type;
        }

        if ($type instanceof TypeScriptUnion && $type->contains(fn (TypeScriptNode $node) => $node instanceof TypeScriptNull)) {
            return $type;
        }

        if ($type instanceof TypeScriptUnion) {
            $type->types[] = new TypeScriptNull();

            return $type;
        }

        return new TypeScriptUnion([$type, new TypeScriptNull()]);
    }

    protected function resolveType(
        ReflectionType $reflectionType,
        ReflectionClass $reflectionClass,
    ): TypeScriptNode {
        return match ($reflectionType::class) {
            ReflectionNamedType::class => $this->reflectionNamedType($reflectionType, $reflectionClass),
            ReflectionUnionType::class => $this->reflectionUnionType($reflectionType, $reflectionClass),
            ReflectionIntersectionType::class => $this->reflectionIntersectionType($reflectionType, $reflectionClass),
            default => new TypeScriptUndefined(),
        };
    }

    protected function reflectionNamedType(
        ReflectionNamedType $type,
        ReflectionClass $reflectionClass,
    ): TypeScriptNode {
        if ($type->getName() === 'string') {
            return new TypeScriptString();
        }

        if ($type->getName() === 'float' || $type->getName() === 'int') {
            return new TypeScriptNumber();
        }

        if ($type->getName() === 'bool' || $type->getName() === 'true' || $type->getName() === 'false') {
            return new TypeScriptBoolean();
        }

        if ($type->getName() === 'array') {
            return new TypeScriptArray([]);
        }

        if ($type->getName() === 'null') {
            return new TypeScriptNull();
        }

        if ($type->getName() === 'mixed') {
            return new TypeScriptAny();
        }

        if ($type->getName() === 'self' || $type->getName() === 'static') {
            return new TypeReference(new ClassStringReference($reflectionClass->getName()));
        }

        if ($type->getName() === 'object') {
            return new TypeScriptObject([]);
        }

        if ($type->getName() === 'void') {
            return new TypeScriptVoid();
        }

        if (class_exists($type->getName()) || interface_exists($type->getName())) {
            return new TypeReference(new ClassStringReference($type->getName()));
        }

        return new TypeScriptUnknown();
    }

    protected function reflectionUnionType(
        ReflectionUnionType $type,
        ReflectionClass $reflectionClass,
    ): TypeScriptNode {
        return new TypeScriptUnion(array_map(
            fn (ReflectionType $type) => $this->resolveType($type, $reflectionClass),
            $type->getTypes()
        ));
    }

    protected function reflectionIntersectionType(
        ReflectionIntersectionType $type,
        ReflectionClass $reflectionClass,
    ): TypeScriptNode {
        return new TypeScriptIntersection(array_map(
            fn (ReflectionType $type) => $this->resolveType($type, $reflectionClass),
            $type->getTypes()
        ));
    }
}
