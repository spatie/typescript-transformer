<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript('JustAnotherName')]
class TypeScriptAttributedClass
{
    public string $property;
}
