<?php

namespace Spatie\TypescriptTransformer\Structures;

use ReflectionClass;

class Type
{
    public ReflectionClass $reflection;

    public string $name;

    public string $transformed;

    public array $missingSymbols;

    public function __construct(
        ReflectionClass $class,
        string $name,
        string $transformed,
        array $missingSymbols
    ) {
        $this->reflection = $class;
        $this->name = $name;
        $this->transformed = $transformed;
        $this->missingSymbols = $missingSymbols;
    }
}
