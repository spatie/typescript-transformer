<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\ImportName;
use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptImport implements TypeScriptNode
{
    /**
     * @param  array<ImportName>  $names
     */
    public function __construct(
        public string $path,
        public array $names,
    ) {
    }

    public function write(WritingContext $context): string
    {
        $names = implode(', ', $this->names);

        return "import { {$names} } from '{$this->path}';".PHP_EOL;
    }
}
