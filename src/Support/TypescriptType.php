<?php

namespace Spatie\TypescriptTransformer\Support;

use phpDocumentor\Reflection\Type;

class TypescriptType implements Type
{
    private string $typescript;

    public static function create(string $typescript): TypescriptType
    {
        return new self($typescript);
    }

    public function __construct(string $typescript)
    {
        $this->typescript = $typescript;
    }

    public function __toString(): string
    {
        return $this->typescript;
    }
}
