<?php

namespace Spatie\TypeScriptTransformer\Tests\FakeClasses\Enum;

use MyCLabs\Enum\Enum;

/**
 * @typescript
 * @typescript-transformer \Spatie\TypeScriptTransformer\Tests\Fakes\FakeTypeScriptTransformer
 */
class TypeScriptEnumWithCustomTransformer extends Enum
{
    const JS = 'js';
}
