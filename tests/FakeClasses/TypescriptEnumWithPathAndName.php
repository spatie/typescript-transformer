<?php

namespace Spatie\TypescriptTransformer\Tests\FakeClasses;

use MyCLabs\Enum\Enum;

/** @typescript EnumWithNameAndPath other/types.d.ts */
class TypescriptEnumWithPathAndName extends Enum
{
    const JS = 'js';
}
