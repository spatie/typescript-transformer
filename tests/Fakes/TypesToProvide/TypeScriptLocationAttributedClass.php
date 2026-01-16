<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(location: ['App', 'Here'])]
class TypeScriptLocationAttributedClass
{
    public string $property;
}
