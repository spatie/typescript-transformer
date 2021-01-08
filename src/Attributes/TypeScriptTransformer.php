<?php

namespace Spatie\TypeScriptTransformer\Attributes;

use Attribute;

#[Attribute]
class TypeScriptTransformer
{
    public string $transformer;

    public function __construct(string $transformer)
    {
        $this->transformer = $transformer;
    }
}
