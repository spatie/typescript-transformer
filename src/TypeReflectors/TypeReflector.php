<?php

namespace Spatie\TypeScriptTransformer\TypeReflectors;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Nullable;
use ReflectionAttribute;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;
use Spatie\TypeScriptTransformer\Attributes\TypeScriptTransformableAttribute;
use Spatie\TypeScriptTransformer\Support\UnknownType;
use Spatie\TypeScriptTransformer\Types\TypeScriptType;

abstract class TypeReflector
{
    abstract protected function getDocblock(): string;

    abstract protected function docblockRegex(): string;

    abstract protected function getReflectionType(): ?ReflectionType;

    abstract protected function getAttributes(): array;

    public static function new(ReflectionMethod|ReflectionProperty|ReflectionParameter $reflection): static
    {
        if ($reflection instanceof ReflectionProperty) {
            return new PropertyTypeReflector($reflection);
        }

        if ($reflection instanceof ReflectionParameter) {
            return new MethodParameterTypeReflector($reflection);
        }

        return new MethodReturnTypeReflector($reflection);
    }

    public function reflect(): Type
    {
        if ($type = $this->reflectionFromAttribute()) {
            return $type;
        }

        if ($type = $this->reflectFromDocblock()) {
            return $type;
        }

        if ($type = $this->reflectFromReflection()) {
            return $type;
        }

        return new TypeScriptType('any');
    }

    private function reflectionFromAttribute(): ?Type
    {
        $attributes = array_filter(
            $this->getAttributes(),
            fn(ReflectionAttribute $attribute) => is_a($attribute->getName(), TypeScriptTransformableAttribute::class, true)
        );

        if (empty($attributes)) {
            return null;
        }

        /** @var \Spatie\TypeScriptTransformer\Attributes\TypeScriptTransformableAttribute $attribute */
        $attribute = current($attributes)->newInstance();

        return $attribute->getType();
    }

    private function reflectFromDocblock(): ?Type
    {
        preg_match(
            $this->docblockRegex(),
            $this->getDocblock(),
            $matches
        );

        $docDefinition = $matches[1] ?? null;

        if ($docDefinition === null) {
            return null;
        }

        $type = (new TypeResolver())->resolve($docDefinition);

        return $this->nullifyType($type);
    }

    private function reflectFromReflection(): ?Type
    {
        $reflectionType = $this->getReflectionType();

        if ($reflectionType === null) {
            return null;
        }

        if ($reflectionType instanceof ReflectionUnionType) {
            $type = new Compound(array_map(
                fn(ReflectionNamedType $reflectionType) => (new TypeResolver())->resolve($reflectionType->getName()),
                $reflectionType->getTypes()
            ));

            return $this->nullifyType($type);
        }

        if (! $reflectionType instanceof ReflectionNamedType) {
            return null;
        }

        $type = (new TypeResolver())->resolve($reflectionType->getName());

        return $this->nullifyType($type);
    }

    private function nullifyType(Type $type): Type
    {
        $reflectionType = $this->getReflectionType();

        if ($reflectionType === null || $reflectionType->allowsNull() === false) {
            return $type;
        }

        if ($type instanceof Nullable) {
            return $type;
        }

        if ($type instanceof Compound && $type->contains(new Null_())) {
            return $type;
        }

        if ($type instanceof Compound) {
            return new Compound(array_merge(
                iterator_to_array($type->getIterator()),
                [new Null_()],
            ));
        }

        return new Nullable($type);
    }
}
