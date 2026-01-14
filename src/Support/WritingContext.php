<?php

namespace Spatie\TypeScriptTransformer\Support;

class WritingContext
{
    /**
     * @param array<string, string> $resolvedReferenceMap
     */
    public function __construct(
        public array $resolvedReferenceMap,
    ) {
    }
}
