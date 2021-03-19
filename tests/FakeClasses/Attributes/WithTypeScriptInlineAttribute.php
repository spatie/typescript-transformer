<?php

namespace Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes;

use MyCLabs\Enum\Enum;
use Spatie\TypeScriptTransformer\Attributes\InlineTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
#[InlineTypeScriptType]
class WithTypeScriptInlineAttribute extends Enum
{
    const A = 'a';
    const B = 'b';
}
