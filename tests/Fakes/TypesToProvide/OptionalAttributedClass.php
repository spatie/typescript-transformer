<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide;

use Spatie\TypeScriptTransformer\Attributes\Optional;

#[Optional]
class OptionalAttributedClass
{
    public string $property;
}
