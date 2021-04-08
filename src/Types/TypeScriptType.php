<?php

namespace Spatie\TypeScriptTransformer\Types;

use phpDocumentor\Reflection\Type;

/** @psalm-immutable */
class TypeScriptType implements Type
{
    private string $typeScript;

    public static function create(string $typeScript): TypeScriptType
    {
        return new self($typeScript);
    }

    public function __construct(string $typeScript)
    {
        $this->typeScript = $typeScript;
    }

    public function __toString(): string
    {
        return $this->typeScript;
    }
}
