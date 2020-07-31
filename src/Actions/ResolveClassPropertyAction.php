<?php

namespace Spatie\TypescriptTransformer\Actions;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\AbstractList;
use phpDocumentor\Reflection\Types\Array_;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use Roave\BetterReflection\Reflection\ReflectionType;
use Spatie\TypescriptTransformer\ValueObjects\ClassProperty;

class ResolveClassPropertyAction
{
    public function execute(ReflectionProperty $reflection): ClassProperty
    {
        $types = [];
        $arrayTypes = [];
        $objectTypes = [];

        $this->resolveFromReflection($reflection->getType(), $types);

        foreach ($reflection->getDocBlockTypes() as $type) {
            $this->resolveFromDocBlockType($type, $types, $arrayTypes, $objectTypes);
        }

        dd($types, $arrayTypes, $objectTypes);

        return ClassProperty::create($reflection, $types, $arrayTypes);
    }

    private function resolveFromReflection(
        ?ReflectionType $reflection,
        array &$types
    ): void {
        if ($reflection === null) {
            $types[] = 'null';

            return;
        }

        if ($reflection->allowsNull()) {
            $types[] = 'null';
        }

        $types[] = $reflection->getName();
    }

    private function resolveFromDocBlockType(
        Type $type,
        array &$types,
        array &$arrayTypes,
        array &$objectTypes
    ) {
        if ($type instanceof Array_) {
            dump($type);
        }
    }

    private function resolveListType(
        AbstractList $type,
        array &$arrayTypes,
        array &$objectTypes
    ) {
    }
}
