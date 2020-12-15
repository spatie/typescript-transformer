<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Exception;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\AbstractList;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\ClassString;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\Scalar;
use phpDocumentor\Reflection\Types\Self_;
use phpDocumentor\Reflection\Types\Static_;
use phpDocumentor\Reflection\Types\String_;
use phpDocumentor\Reflection\Types\This;
use phpDocumentor\Reflection\Types\Void_;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\Support\TypeScriptType;

class TranspileTypeToTypeScriptAction
{
    private MissingSymbolsCollection $missingSymbolsCollection;

    private string $currentClass;

    public function __construct(
        MissingSymbolsCollection $missingSymbolsCollection,
        string $currentClass
    ) {
        $this->missingSymbolsCollection = $missingSymbolsCollection;
        $this->currentClass = $currentClass;
    }

    public function execute(Type $type): string
    {
        if ($type instanceof Compound) {
            return $this->resolveCompoundType($type);
        }

        if ($type instanceof AbstractList) {
            return $this->resolveListType($type);
        }

        if ($type instanceof Nullable) {
            return $this->resolveNullableType($type);
        }

        if ($type instanceof Object_) {
            return $this->resolveObjectType($type);
        }

        if ($type instanceof TypeScriptType) {
            return (string) $type;
        }

        if ($type instanceof Boolean) {
            return 'boolean';
        }

        if ($type instanceof Float_ || $type instanceof Integer) {
            return 'number';
        }

        if ($type instanceof String_ || $type instanceof ClassString) {
            return 'string';
        }

        if ($type instanceof Null_) {
            return 'null';
        }

        if ($type instanceof Self_ || $type instanceof Static_ || $type instanceof This) {
            return $this->missingSymbolsCollection->add($this->currentClass);
        }

        if ($type instanceof Scalar) {
            return 'string|number|boolean';
        }

        if ($type instanceof Mixed_) {
            return 'any';
        }

        if ($type instanceof Void_) {
            return 'never';
        }

        throw new Exception("Could not transform type: {$type}");
    }

    private function resolveCompoundType(Compound $compound): string
    {
        $transformed = array_map(
            fn (Type $type) => $this->execute($type),
            iterator_to_array($compound->getIterator())
        );

        return join(' | ', $transformed);
    }

    private function resolveListType(AbstractList $list): string
    {
        if ($this->isTypeScriptArray($list->getKeyType())) {
            return "Array<{$this->execute($list->getValueType())}>";
        }

        return "{ [key: {$this->execute($list->getKeyType())}]: {$this->execute($list->getValueType())} }";
    }

    private function resolveNullableType(Nullable $nullable): string
    {
        return "{$this->execute($nullable->getActualType())} | null";
    }

    private function resolveObjectType(Object_ $object)
    {
        if ($object->getFqsen() === null) {
            return 'object';
        }

        return $this->missingSymbolsCollection->add(
            (string) $object->getFqsen()
        );
    }

    private function isTypeScriptArray(Type $keyType): bool
    {
        if (! $keyType instanceof Compound) {
            return false;
        }

        if ($keyType->getIterator()->count() !== 2) {
            return false;
        }

        if (! $keyType->contains(new String_()) || ! $keyType->contains(new Integer())) {
            return false;
        }

        return true;
    }
}
