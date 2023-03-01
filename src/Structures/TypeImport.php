<?php

namespace Spatie\TypeScriptTransformer\Structures;

class TypeImport
{
    /**
     * @param string[] $types
     */
    public function __construct(
        public string $file,
        public array $types,
    )
    {
    }
}
