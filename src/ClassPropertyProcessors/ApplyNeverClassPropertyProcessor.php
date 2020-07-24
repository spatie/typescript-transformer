<?php

namespace Spatie\TypescriptTransformer\ClassPropertyProcessors;

use Spatie\TypescriptTransformer\ValueObjects\ClassProperty;

class ApplyNeverClassPropertyProcessor implements ClassPropertyProcessor
{
    public function process(ClassProperty $classProperty): ClassProperty
    {
        if ($this->shouldApply($classProperty)) {
            return ClassProperty::create($classProperty->reflection, ['never'], []);
        }

        return $classProperty;
    }

    protected function shouldApply(ClassProperty $classProperty): bool
    {
        if (count($classProperty->arrayTypes) > 0 && $classProperty->arrayTypes !== ['null']) {
            return false;
        }

        if (count($classProperty->types) > 0 && $classProperty->types !== ['null']) {
            return false;
        }

        return true;
    }
}
