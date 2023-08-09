<?php

namespace Spatie\TypeScriptTransformer\Support;

use Spatie\TypeScriptTransformer\References\Reference;

class ImportName
{
    public function __construct(
        public string $name,
        public Reference $reference,
        public ?string $alias = null,
    ) {
    }

    public function __toString(): string
    {
        if ($this->alias === null) {
            return $this->name;
        }

        return "{$this->name} as {$this->alias}";
    }

    public function isAliased(): bool
    {
        return $this->alias !== null;
    }
}
