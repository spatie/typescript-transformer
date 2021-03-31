<?php

namespace Spatie\TypeScriptTransformer\TypeProcessors;

use phpDocumentor\Reflection\Type;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;

interface TypeProcessor
{
    public function process(
        Type $type,
        ReflectionProperty|ReflectionParameter|ReflectionMethod $reflection,
        MissingSymbolsCollection $missingSymbolsCollection
    ): ?Type;
}
