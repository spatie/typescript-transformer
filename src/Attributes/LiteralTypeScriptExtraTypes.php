<?php

namespace Spatie\TypeScriptTransformer\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class LiteralTypeScriptExtraTypes
{
    private array $extras;

    public function __construct(array $extras)
    {
        $this->extras = $extras;
    }

    public function getExtras(): array
    {
        return $this->extras;
    }
}
