<?php

namespace Spatie\TypescriptTransformer\Structures;

use ReflectionClass;

class TransformedType
{
    public string $transformed;

    public array $missingSymbols;

    public static function create(
        string $transformed,
        array $missingSymbols = []
    )
    {
        return new self($transformed, $missingSymbols);
    }

    public function __construct(
        string $transformed,
        array $missingSymbols = []
    ) {
        $this->transformed = $transformed;
        $this->missingSymbols = $missingSymbols;
    }
}
