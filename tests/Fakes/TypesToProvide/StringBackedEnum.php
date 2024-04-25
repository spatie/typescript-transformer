<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide;

enum StringBackedEnum: string
{
    case John = 'john';
    case Paul = 'paul';
    case George = 'george';
    case Ringo = 'ringo';
}
