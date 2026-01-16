<?php

namespace Spatie\TypeScriptTransformer\Data;

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
