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

    public function getNamespaceSegments(): array
    {
        $namespace = $this->reflection->getNamespaceName();

        if (empty($namespace)) {
            return [];
        }

        return explode('\\', $namespace);
    }

    public function getTypescriptName(): string
    {
        $segments = array_merge(
            $this->getNamespaceSegments(),
            [$this->name]
        );

        return implode('.', $segments);
    }
}
