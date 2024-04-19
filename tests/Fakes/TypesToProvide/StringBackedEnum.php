<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide;

enum StringBackedEnum: string
{
    case John = 'John';
    case Paul = 'Paul';
    case George = 'George';
    case Ringo = 'Ringo';
}
