<?php

namespace Spatie\TypeScriptTransformer\Attributes;

use Attribute;

#[Attribute]
class TypeScript
{
    public ?string $name;

    public function __construct(?string $name = null)
    {
        $this->name = $name;
    }
}
