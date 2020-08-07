<?php

namespace Spatie\TypescriptTransformer\ClassPropertyProcessors;

use phpDocumentor\Reflection\Type;

interface ClassPropertyProcessor
{
    public function process(Type $type): Type;
}
