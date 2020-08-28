<?php

namespace Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration;

use MyCLabs\Enum\Enum as BaseEnum;

/** @typescript */
class Enum extends BaseEnum
{
    public const yes = 'yes';
    public const no = 'no';
}
