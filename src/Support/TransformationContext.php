<?php

namespace Spatie\TypeScriptTransformer\Support;

class TransformationContext
{
    public function __construct(
        public string $name,
        public array $nameSpaceSegments,
    ) {
    }
}
