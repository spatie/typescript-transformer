<?php

namespace Spatie\TypeScriptTransformer\Tests\FakeClasses;

/**
 * @typescript
 */
enum IntBackedEnum: int
{
    case JS = 1;
    case PHP = 2;
}
