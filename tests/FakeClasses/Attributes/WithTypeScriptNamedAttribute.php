<?php

namespace Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes;

use MyCLabs\Enum\Enum;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript('YoloClass')]
class WithTypeScriptNamedAttribute extends Enum
{
    const A = 'a';
    const B = 'b';
}
