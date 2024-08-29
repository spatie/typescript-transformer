<?php

namespace Spatie\TypeScriptTransformer\Attributes;

use ReflectionClass;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;

interface TypeScriptTypeAttributeContract
{
    public function getType(ReflectionClass $class): TypeScriptNode;
}
