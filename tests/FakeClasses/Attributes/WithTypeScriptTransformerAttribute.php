<?php

namespace Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Attributes\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\Transformers\DtoTransformer;

#[TypeScript]
#[TypeScriptTransformer(DtoTransformer::class)]
class WithTypeScriptTransformerAttribute
{
    public int $an_int;
}
