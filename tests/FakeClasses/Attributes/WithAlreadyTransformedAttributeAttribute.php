<?php

namespace Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Attributes\TypeScriptType;

#[TypeScript]
#[TypeScriptType(['an_int' => 'int', 'a_bool' => 'bool'])]
class WithAlreadyTransformedAttributeAttribute
{
}
