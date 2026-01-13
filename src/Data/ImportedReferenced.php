<?php

namespace Spatie\TypeScriptTransformer\Data;

class ImportedReferenced
{
    public function __construct(
        public string $name,
        public string $outputPath,
    ) {
    }
}
