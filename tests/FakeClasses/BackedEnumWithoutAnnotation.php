<?php

namespace Spatie\TypeScriptTransformer\Tests\FakeClasses;

enum BackedEnumWithoutAnnotation: string
{
    case FOO = 'foo';
    case BAR = 'bar';
}
