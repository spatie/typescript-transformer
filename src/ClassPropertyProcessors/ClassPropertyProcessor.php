<?php

namespace Spatie\TypescriptTransformer\ClassPropertyProcessors;

use Spatie\TypescriptTransformer\ValueObjects\ClassProperty;

interface ClassPropertyProcessor
{
    public function process(ClassProperty $classProperty): ClassProperty;
}
