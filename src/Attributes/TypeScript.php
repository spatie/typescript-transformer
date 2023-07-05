<?php

namespace Spatie\TypeScriptTransformer\Attributes;

use Attribute;

#[Attribute]
class TypeScript
{
    /**
     * @param array<string>|null $location
     */
    public function __construct(
        public ?string $name = null,
        public ?array $location = null,
    ) {
    }
}
