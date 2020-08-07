<?php

namespace Spatie\TypescriptTransformer\ClassPropertyProcessors;

use phpDocumentor\Reflection\Type;
use Spatie\TypescriptTransformer\ValueObjects\ClassProperty;

interface ClassPropertyProcessor
{
    public function process(Type $type): Type;
}
