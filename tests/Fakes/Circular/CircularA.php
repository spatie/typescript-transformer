<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes\Circular;

class CircularA
{
    public CircularB $b;
}
