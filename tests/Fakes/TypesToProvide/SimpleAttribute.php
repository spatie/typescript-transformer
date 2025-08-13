<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide;

use Attribute;

#[Attribute]
class SimpleAttribute
{
    public function __construct(
        public int $argument,
        public int $default = 42,
    ) {
    }
}
