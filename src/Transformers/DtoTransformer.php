<?php

namespace Spatie\TypescriptTransformer\Transformers;

use ReflectionClass;
use Spatie\DataTransferObject\DataTransferObject;
use Spatie\TypescriptTransformer\ClassPropertyProcessors\ApplyNeverClassPropertyProcessor;
use Spatie\TypescriptTransformer\ClassPropertyProcessors\CleanupClassPropertyProcessor;

class DtoTransformer extends ClassTransformer
{
    public function canTransform(ReflectionClass $class): bool
    {
        return is_subclass_of($class->getName(), DataTransferObject::class);
    }

    protected function getClassPropertyProcessors(): array
    {
        return [
            new CleanupClassPropertyProcessor(),
            new ApplyNeverClassPropertyProcessor(),
        ];
    }
}
