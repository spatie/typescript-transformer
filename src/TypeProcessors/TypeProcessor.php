<?php

namespace Spatie\TypeScriptTransformer\TypeProcessors;

use phpDocumentor\Reflection\Type;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Spatie\TypeScriptTransformer\Structures\TypeReferencesCollection;

interface TypeProcessor
{
    public function process(
        Type $type,
        ReflectionProperty | ReflectionParameter | ReflectionMethod $reflection,
        TypeReferencesCollection $typeReferences
    ): ?Type;
}
