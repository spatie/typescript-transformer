<?php

namespace Spatie\TypeScriptTransformer\Support;

use Spatie\TypeScriptTransformer\Transformed\Transformed;

class Location
{
    /**
     * @param  array<string>  $segments
     * @param  array<Transformed>  $transformed
     */
    public function __construct(
        public array $segments,
        public array $transformed,
    ) {
    }
}
