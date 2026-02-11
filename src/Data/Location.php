<?php

namespace Spatie\TypeScriptTransformer\Data;

use Spatie\TypeScriptTransformer\Transformed\Transformed;

class Location
{
    /**
     * @param array<string> $path
     * @param array<Transformed> $transformed
     * @param array<Location> $children
     */
    public function __construct(
        public string $name,
        public array $path,
        public array $transformed,
        public array $children = [],
    ) {
    }
}
