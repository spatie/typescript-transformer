<?php

namespace Spatie\TypescriptTransformer\Tests\FakeClasses\Enum;

use MyCLabs\Enum\Enum;

/**
 * @typescript
 * @typescript-transformer \Spatie\TypescriptTransformer\Tests\Fakes\FakeTypescriptTransformer
 */
class TypescriptEnumWithCustomTransformer extends Enum
{
    const JS = 'js';
}
