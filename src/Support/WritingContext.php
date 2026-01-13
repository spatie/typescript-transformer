<?php

namespace Spatie\TypeScriptTransformer\Support;

class WritingContext
{
    /**
     * @param array<string, string> $nameMap Map of reference keys to resolved names
     */
    public function __construct(
        public array $nameMap,
    ) {
    }
}
