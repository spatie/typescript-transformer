<?php

namespace Spatie\TypescriptTransformer\Tests\FakeClasses;

use MyCLabs\Enum\Enum;

/** @typescript other/types.d.ts */
class TypescriptEnumWithPath extends Enum
{
    const JS = 'js';
}
