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
use Spatie\TypeScriptTransformer\Types\RecordType;
use Spatie\TypeScriptTransformer\Types\StructType;
use Spatie\TypeScriptTransformer\Types\TypeScriptType;

class TranspileTypeToTypeScriptAction
{
    private MissingSymbolsCollection $missingSymbolsCollection;

    private ?string $currentClass;

    public function __construct(
        MissingSymbolsCollection $missingSymbolsCollection,
        ?string $currentClass = null
    ) {
        $this->missingSymbolsCollection = $missingSymbolsCollection;
        $this->currentClass = $currentClass;
    }

    public function execute(Type $type): string
    {
        return match (true) {
            $type instanceof Compound => $this->resolveCompoundType($type),
            $type instanceof AbstractList => $this->resolveListType($type),
            $type instanceof Nullable => $this->resolveNullableType($type),
            $type instanceof Object_ => $this->resolveObjectType($type),
            $type instanceof StructType => $this->resolveStructType($type),
            $type instanceof RecordType => $this->resolveRecordType($type),
            $type instanceof TypeScriptType => (string) $type,
            $type instanceof Boolean => 'boolean',
            $type instanceof Float_, $type instanceof Integer => 'number',
            $type instanceof String_, $type instanceof ClassString => 'string',
            $type instanceof Null_ => 'null',
            $type instanceof Self_, $type instanceof Static_, $type instanceof This => $this->resolveSelfReferenceType(),
            $type instanceof Scalar => 'string|number|boolean',
            $type instanceof Mixed_ => 'any',
            $type instanceof Void_ => 'void',
            default => throw new Exception("Could not transform type: {$type}")
        };
    }

    private function resolveCompoundType(Compound $compound): string
    {
        $transformed = array_map(
            fn (Type $type) => $this->execute($type),
            iterator_to_array($compound->getIterator())
        );

        return join(' | ', array_unique($transformed));
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

    private function resolveObjectType(Object_ $object): string
    {
        if ($object->getFqsen() === null) {
            return 'object';
        }

        return $this->missingSymbolsCollection->add(
            (string) $object->getFqsen()
        );
    }

    private function resolveStructType(StructType $type): string
    {
        $transformed = "{";

        foreach ($type->getTypes() as $name => $type) {
            $transformed .= "{$name}:{$this->execute($type)};";
        }

        return "{$transformed}}";
    }

    private function resolveRecordType(RecordType $type): string
    {
        return "Record<{$this->execute($type->getKeyType())}, {$this->execute($type->getValueType())}>";
    }

    private function resolveSelfReferenceType(): string
    {
        if ($this->currentClass === null) {
            return 'any';
        }

        return $this->missingSymbolsCollection->add($this->currentClass);
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
