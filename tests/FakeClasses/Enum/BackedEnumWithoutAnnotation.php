<?php

namespace Spatie\TypeScriptTransformer\Tests\FakeClasses\Enum;

enum BackedEnumWithoutAnnotation: string
{
    case FOO = 'foo';
    case BAR = 'bar';
}
