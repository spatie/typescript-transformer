<?php

namespace Spatie\TypeScriptTransformer\Attributes;

class AdditionalImport
{
    /** @var array<string> */
    public readonly array $names;

    /** @param string|array<string> $names */
    public function __construct(
        public readonly string $path,
        string|array $names,
    ) {
        $this->names = is_array($names) ? $names : [$names];
    }

    /** @return array<string, string>  */
    public function getReferenceKeys(): array
    {
        $keys = [];

        foreach ($this->names as $name) {
            $keys[$name] = "additional_import:{$this->path}:{$name}";
        }

        return $keys;
    }
}
