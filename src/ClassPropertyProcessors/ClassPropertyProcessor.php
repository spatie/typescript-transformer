<?php

namespace Spatie\TypescriptTransformer\ClassPropertyProcessors;

use phpDocumentor\Reflection\Type;
use ReflectionProperty;

interface ClassPropertyProcessor
{
    public function process(Type $type, ReflectionProperty $reflection): Type;
}
