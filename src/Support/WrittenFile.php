<?php

namespace Spatie\TypeScriptTransformer\Support;

class WrittenFile
{
    public function __construct(
        public string $path,
        public array $types,
    ) {
    }
}
