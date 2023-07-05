<?php

namespace Spatie\TypeScriptTransformer\Attributes;

use ReflectionClass;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;

interface TypeScriptTypeAttributeContract
{
    public function getType(ReflectionClass $class): TypeScriptNode;
}
