<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide;

use Attribute;

#[Attribute]
class ComplexAttribute
{
    public function __construct(
        public int $argumentA,
        public int $argumentB,
        public string $argumentC = 'C',
        public string $argumentD = 'D',
    ) {
    }
}
