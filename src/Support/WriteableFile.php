<?php

namespace Spatie\TypeScriptTransformer\Support;

class WriteableFile
{
    public function __construct(
        public string $path,
        public string $contents,
    ) {
    }
}
