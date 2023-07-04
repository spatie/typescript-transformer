<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptImport implements TypeScriptNode
{
    public function __construct(
        public string $path,
        public array $names,
    )
    {
    }

    public function write(WritingContext $context): string
    {
        $names = implode(', ', $this->names);

        return "import { {$names} } from '{$this->path}';" . PHP_EOL;
    }
}
