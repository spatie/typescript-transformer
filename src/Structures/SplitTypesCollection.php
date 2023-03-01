<?php

namespace Spatie\TypeScriptTransformer\Structures;

class SplitTypesCollection
{
    public function __construct(
        public string $path,
        public TypesCollection $types,
        public array $imports,
    ) {
    }
}
