<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Exception;
use phpDocumentor\Reflection\PseudoType;
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
use SebastianBergmann\ObjectReflector\ObjectReflector;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\Structures\TranspilationResult;
use Spatie\TypeScriptTransformer\Types\RecordType;
use Spatie\TypeScriptTransformer\Types\StructType;
use Spatie\TypeScriptTransformer\Types\TypeScriptType;

class TranspileTypeToTypeScriptAction
{
    private MissingSymbolsCollection $missingSymbolsCollection;

    private ?string $currentClass;

    private bool $nullablesAreOptional;

    public function __construct(
        MissingSymbolsCollection $missingSymbolsCollection,
        bool $nullablesAreOptional = false,
        ?string $currentClass = null
    ) {
        $this->missingSymbolsCollection = $missingSymbolsCollection;
        $this->nullablesAreOptional = $nullablesAreOptional;
        $this->currentClass = $currentClass;
    }

    public function execute(Type $type): TranspilationResult {
        $result = match (true) {
            $type instanceof Compound => $this->resolveCompoundType($type),
            $type instanceof AbstractList => $this->resolveListType($type),
            $type instanceof Nullable => $this->resolveNullableType($type),
            $type instanceof Object_ => $this->resolveObjectType($type),
            $type instanceof StructType => $this->resolveStructType($type),
            $type instanceof RecordType => $this->resolveRecordType($type),
            $type instanceof TypeScriptType => TranspilationResult::noDeps((string)$type),
            $type instanceof Boolean => TranspilationResult::noDeps('boolean'),
            $type instanceof Float_, $type instanceof Integer => TranspilationResult::noDeps('number'),
            $type instanceof String_, $type instanceof ClassString => TranspilationResult::noDeps('string'),
            $type instanceof Null_ => TranspilationResult::noDeps('null'),
            $type instanceof Self_, $type instanceof Static_, $type instanceof This => $this->resolveSelfReferenceType(),
            $type instanceof Scalar => TranspilationResult::noDeps('string|number|boolean'),
            $type instanceof Mixed_ => TranspilationResult::noDeps('any'),
            $type instanceof Void_ => TranspilationResult::noDeps('void'),
            $type instanceof PseudoType => $this->execute($type->underlyingType()),
            default => throw new Exception("Could not transform type: {$type}")
        };
        return new TranspilationResult(
            array_merge(
                (
                    $type instanceof Object_
                    && $type->__toString() !== 'object'
                )
                    ? [$type]
                    : [],
                $result->dependencies
            ),
            $result->typescript
        );
    }

    private function resolveCompoundType(Compound $compound): TranspilationResult {
        $transformed = array_map(
            fn(Type $type) => $this->execute($type),
            iterator_to_array($compound->getIterator())
        );

        return new TranspilationResult(
            array_reduce(
                $transformed,
                fn(array $carry, TranspilationResult $item) => array_merge(
                    $carry,
                    $item->dependencies
                ),
                []
            ),
            join(
                ' | ',
                array_unique(
                    array_map(
                        fn(TranspilationResult $result) => $result->typescript,
                        $transformed
                    )
                )
            )
        );
    }

    private function resolveListType(AbstractList $list): TranspilationResult {
        $valueTransResult = $this->execute($list->getValueType());
        if ($this->isTypeScriptArray($list->getKeyType())) {
            return new TranspilationResult(
                $valueTransResult->dependencies,
                "Array<$valueTransResult->typescript>"
            );
        }

        $keyTransResult = $this->execute($list->getKeyType());
        $typescript = "{ [key: $keyTransResult->typescript]: {$valueTransResult->typescript} }";
        return new TranspilationResult(
            array_merge($valueTransResult->dependencies, $keyTransResult->dependencies),
            $typescript
        );
    }

    private function resolveNullableType(Nullable $nullable): TranspilationResult {
        if ($this->nullablesAreOptional) {
            return $this->execute($nullable->getActualType());
        }

        $transpilationResult = $this->execute($nullable->getActualType());
        return new TranspilationResult(
            $transpilationResult->dependencies,
            "$transpilationResult->typescript | null"
        );
    }

    private function resolveObjectType(Object_ $object): TranspilationResult {
        if ($object->getFqsen() === null) {
            return TranspilationResult::noDeps('object');
        }

        return TranspilationResult::noDeps(
            $this->missingSymbolsCollection->add(
                (string)$object->getFqsen()
            )
        );
    }

    private function resolveStructType(StructType $type): TranspilationResult {
        $transformed = "{";

        $dependencies = [];
        foreach ($type->getTypes() as $name => $type) {
            $trRes = $this->execute($type);
            $transformed .= "{$name}:{$trRes->typescript};";
            foreach ($trRes->dependencies as $dependency) {
                $dependencies[] = $dependency;
            }
        }

        return new TranspilationResult($dependencies, "{$transformed}}");
    }

    private function resolveRecordType(RecordType $type): TranspilationResult {
        $keyTr = $this->execute($type->getKeyType());
        $valueTr = $this->execute($type->getValueType());
        return new TranspilationResult(
            array_merge($keyTr->dependencies, $valueTr->dependencies),
            "Record<{$keyTr->typescript}, {$valueTr->typescript}>"
        );
    }

    private function resolveSelfReferenceType(): TranspilationResult {
        if ($this->currentClass === null) {
            return TranspilationResult::noDeps('any');
        }

        return TranspilationResult::noDeps(
            $this->missingSymbolsCollection->add($this->currentClass)
        );
    }

    private function isTypeScriptArray(Type $keyType): bool {
        if (!$keyType instanceof Compound) {
            return false;
        }

        if ($keyType->getIterator()->count() !== 2) {
            return false;
        }

        if (!$keyType->contains(new String_()) || !$keyType->contains(new Integer())) {
            return false;
        }

        return true;
    }
}
