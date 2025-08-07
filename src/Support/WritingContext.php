<?php

namespace Spatie\TypeScriptTransformer\Support;

use Closure;
use Spatie\TypeScriptTransformer\References\Reference;

class WritingContext
{
    /**
     * @param Closure(Reference):string  $referenceWriter
     */
    public function __construct(
        public Closure $referenceWriter,
    ) {
    }
}
