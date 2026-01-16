<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide;

use Attribute;

#[Attribute]
class VariadicAttribute
{
    public function __construct(
        public int $argument,
        int ...$variadic
    ) {
    }
}
