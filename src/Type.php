<?php

namespace Spatie\TypescriptTransformer;

use ReflectionClass;

class Type
{
    public ReflectionClass $class;

    public string $file;

    public string $name;

    public string $transformed;

    public function __construct(
        ReflectionClass $class,
        string $file,
        string $name,
        string $transformed
    ) {
        $this->class = $class;
        $this->file = $file;
        $this->name = $name;
        $this->transformed = $transformed;
    }
}
