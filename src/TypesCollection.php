<?php

namespace Spatie\TypescriptTransformer;

use Spatie\TypescriptTransformer\Exceptions\TypeAlreadyExists;

class TypesCollection
{
    private array $map = [];

    public function add(Type $type): self
    {
        if (! array_key_exists($type->file, $this->map)) {
            $this->map[$type->file] = [];
        }

        if (array_key_exists($type->name, $this->map[$type->file])) {
            throw TypeAlreadyExists::create(
                $this->map[$type->file][$type->name],
                $type
            );
        }

        $this->map[$type->file][$type->name] = $type;

        return $this;
    }

    public function get(): array
    {
        return $this->map;
    }
}
