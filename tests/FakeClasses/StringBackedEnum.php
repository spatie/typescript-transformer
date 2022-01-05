<?php

namespace Spatie\TypeScriptTransformer\Tests\FakeClasses;

/**
 * @typescript
 */
enum StringBackedEnum: string
{
    case JS = 'js';
    case PHP = 'php';
}

