<?php

namespace Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes;

use Spatie\TypeScriptTransformer\Attributes\TransformAsTypescript;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Attributes\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\Transformers\DtoTransformer;

#[TypeScript]
#[TransformAsTypescript(['an_int' => 'int', 'a_bool' => 'bool'])]
class WithAlreadyTransformedAttributeAttribute
{
}
