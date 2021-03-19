<?php

namespace Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes;

use Spatie\TypeScriptTransformer\Attributes\TypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Attributes\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\Transformers\DtoTransformer;

#[TypeScript]
#[TypeScriptType(['an_int' => 'int', 'a_bool' => 'bool'])]
class WithAlreadyTransformedAttributeAttribute
{
}
