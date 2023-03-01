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
    ) {
    }

    public function toString(): string
    {
        $implodedTypes = implode(',', $this->types);

        return "import type {{$implodedTypes}} from '{$this->file}'";
    }
}
