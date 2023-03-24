<?php

namespace Spatie\TypeScriptTransformer\Tests\FakeClasses;

use MyCLabs\Enum\Enum;

class MyclabsEnum extends Enum
{
    private const VIEW = 'view';
    private const EDIT = 'edit';
};
