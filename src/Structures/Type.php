<?php

namespace Spatie\TypescriptTransformer\Structures;

use ReflectionClass;

class Type
{
    public ReflectionClass $reflection;

    public string $name;

    public string $transformed;

    public array $missingSymbols;

    public bool $isInline;

    public bool $isCompletelyReplaced = false;

    public function __construct(
        ReflectionClass $class,
        string $name,
        string $transformed,
        array $missingSymbols,
        bool $isInline
    ) {
        $this->reflection = $class;
        $this->name = $name;
        $this->transformed = $transformed;
        $this->missingSymbols = $missingSymbols;
        $this->isInline = $isInline;

        if (empty($this->missingSymbols)) {
            $this->isCompletelyReplaced = true;
        }
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

    public function replaceSymbol(string $class, string $replacement)
    {
        if (in_array($class, $this->missingSymbols)) {
            unset($this->missingSymbols[array_search($class, $this->missingSymbols)]);
        }

        $this->transformed = str_replace(
            "{%{$class}%}",
            $replacement,
            $this->transformed
        );
    }

    public function isCompletelyReplaced()
    {
        return $this->isCompletelyReplaced; // Check of er nog missing symbols
    }
}
