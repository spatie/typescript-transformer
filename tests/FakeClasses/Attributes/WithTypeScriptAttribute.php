<?php

namespace Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes;

use MyCLabs\Enum\Enum;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class WithTypeScriptAttribute extends Enum
{

}
