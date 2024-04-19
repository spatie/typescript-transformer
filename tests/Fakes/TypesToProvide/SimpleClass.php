<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide;

class SimpleClass
{
    public string $stringProperty;

    public function __construct(
        public string $constructorPromotedStringProperty
    ) {
    }
}
