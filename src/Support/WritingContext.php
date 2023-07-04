<?php

namespace Spatie\TypeScriptTransformer\Support;

use Closure;
use Spatie\TypeScriptTransformer\References\Reference;

class WritingContext
{
    /**
     * @param  callable(Reference):string  $referenceWriter
     */
    public function __construct(
        public Closure $referenceWriter,
    ) {
    }
}
