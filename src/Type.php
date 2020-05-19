<?php

namespace Spatie\TypescriptTransformer;

use ReflectionClass;

class Type
{
    public ReflectionClass $class;

    public string $file;

    public string $name;

    public array $options;

    public function __construct(
        ReflectionClass $class,
        string $file,
        string $name,
        array $options
    ) {
        $this->class = $class;
        $this->file = $file;
        $this->name = $name;
        $this->options = $options;
    }
}
