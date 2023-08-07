<?php

namespace Spatie\TypeScriptTransformer\Support;

class ImportName
{
    public function __construct(
        public string $name,
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

    public static function fromNameAndImportedName(
        string $name,
        string $importedName,
    ): self {
        return new self($name, $name === $importedName ? null : $importedName);
    }
}
