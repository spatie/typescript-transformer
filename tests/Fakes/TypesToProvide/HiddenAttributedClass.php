<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide;

use Spatie\TypeScriptTransformer\Attributes\Hidden;

#[Hidden]
class HiddenAttributedClass
{
    public string $property;
}
