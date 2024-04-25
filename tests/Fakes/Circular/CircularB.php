<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes\Circular;

class CircularB
{
    public CircularA $a;
}
