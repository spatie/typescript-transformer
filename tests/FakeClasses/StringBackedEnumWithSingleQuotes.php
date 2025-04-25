<?php

namespace Spatie\TypeScriptTransformer\Tests\FakeClasses;

enum StringBackedEnumWithSingleQuotes: string
{
    case NO_QUOTE = 'no quote';
    case HAS_QUOTE = 'has quote \'';
}
