<?php

namespace Spatie\TypescriptTransformer\ClassPropertyProcessors;

use Closure;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\AbstractList;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\String_;
use Spatie\TypescriptTransformer\PropertyTypesIterator;
use Spatie\TypescriptTransformer\Support\TypescriptType;
use Spatie\TypescriptTransformer\ValueObjects\ClassProperty;

class ApplyNeverClassPropertyProcessor implements ClassPropertyProcessor
{
    use ProcessesClassProperties;

    public function process(Type $type): Type
    {
        if ($this->shouldReplaceType($type)) {
            return new TypescriptType('never');
        }

        return $this->walk($type, function (Type $type) {
            if (! $type instanceof AbstractList) {
                return $type;
            }

            return $this->shouldReplaceType($type->getValueType())
                ? $this->updateListType($type, new TypescriptType('never'))
                : $type;
        });
    }

    private function shouldReplaceType(Type $type): bool
    {
        return $type instanceof Null_ || $type instanceof Mixed_;
    }
}
