<?php

namespace Spatie\TypeScriptTransformer\Support;

readonly class WriteableFile
{
    public string $hash;

    public function __construct(
        public string $path,
        public string $contents,
        public bool $changed = false,
    ) {
        $this->hash = md5($contents);
    }
}
