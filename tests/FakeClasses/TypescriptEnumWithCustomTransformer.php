<?php

namespace Spatie\TypescriptTransformer\Tests\FakeClasses;

use MyCLabs\Enum\Enum;

/**
 * @typescript
 * @typescript-transformer \Spatie\TypescriptTransformer\Tests\FakeClasses\FakeTypescriptTransformer
 */
class TypescriptEnumWithCustomTransformer extends Enum
{
    const JS = 'js';
}
