<?php

namespace Spatie\TypescriptTransformer\Actions;

use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\AbstractList;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionProperty;
use ReflectionType;

class ResolveClassPropertyTypeAction
{
    private TypeResolver $typeResolver;

    public function __construct(TypeResolver $typeResolver)
    {
        $this->typeResolver = $typeResolver;
    }

    public function execute(ReflectionProperty $reflection): Type
    {
        $types = $this->resolveFromDocBlock($reflection);

        if ($reflectionType = $reflection->getType()) {
            $types = $this->appendReflectionType($reflectionType, $types);
            $types = $this->nullifyTypes($reflectionType, $types);
        }

        return ! empty($types)
            ? new Compound(array_values($types))
            : new Mixed_();
    }

    public function resolveFromDocBlock(ReflectionProperty $reflection): array
    {
        preg_match(
            '/@var ((?:(?:[\w?|\\\\<>])+(?:\[])?)+)/',
            $reflection->getDocComment(),
            $matches
        );

        $docDefinition = $matches[1] ?? null;

        if ($docDefinition === null) {
            return [];
        }

        $type = $this->typeResolver->resolve($docDefinition);

        return $type instanceof Compound
            ? iterator_to_array($type->getIterator())
            : [$type];
    }

    private function appendReflectionType(
        ReflectionType $reflection,
        array $types
    ): array {
        $type = $reflection->isBuiltin()
            ? $this->typeResolver->resolve($reflection->getName())
            : new Object_(new Fqsen('\\' . $reflection->getName()));

        if ($this->shouldIgnoreReflectedType($type, $types)) {
            return $types;
        }

        return array_merge([$type], $types);
    }

    private function shouldIgnoreReflectedType(
        Type $type,
        array $types
    ): bool {
        if (! $type instanceof Array_) {
            return false;
        }

        $hasAlreadyAListType = array_reduce(
            $types,
            function (bool $carry, Type $type) {
                if ($type instanceof Nullable) {
                    $type = $type->getActualType();
                }

                return $carry || $type instanceof AbstractList;
            },
            false
        );

        return $hasAlreadyAListType;
    }

    private function nullifyTypes(ReflectionType $reflection, array $types): array
    {
        if (! $reflection->allowsNull()) {
            return $types;
        }

        return array_map(function (Type $type) {
            if ($type instanceof Nullable || $type instanceof Null_) {
                return $type;
            }

            return new Nullable($type);
        }, $types);
    }
}
