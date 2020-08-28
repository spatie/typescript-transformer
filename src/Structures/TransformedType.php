<?php

namespace Spatie\TypeScriptTransformer\Structures;

use ReflectionClass;

class TransformedType
{
    public ReflectionClass $reflection;

    public ?string $name = null;

    public string $transformed;

    public MissingSymbolsCollection $missingSymbols;

    public bool $isInline;

    public static function create(
        ReflectionClass $class,
        string $name,
        string $transformed,
        ?MissingSymbolsCollection $missingSymbols = null,
        bool $inline = false
    ): self {
        return new self($class, $name, $transformed, $missingSymbols ?? new MissingSymbolsCollection(), $inline);
    }

    public static function createInline(
        ReflectionClass $class,
        string $transformed,
        ?MissingSymbolsCollection $missingSymbols = null
    ): self {
        return new self($class, null, $transformed, $missingSymbols ?? new MissingSymbolsCollection(), true);
    }

    public function __construct(
        ReflectionClass $class,
        ?string $name,
        string $transformed,
        MissingSymbolsCollection $missingSymbols,
        bool $isInline
    ) {
        $this->reflection = $class;
        $this->name = $name;
        $this->transformed = $transformed;
        $this->missingSymbols = $missingSymbols;
        $this->isInline = $isInline;
    }

    public function getNamespaceSegments(): array
    {
        if ($this->isInline === true) {
            return [];
        }

        $namespace = $this->reflection->getNamespaceName();

        if (empty($namespace)) {
            return [];
        }

        return explode('\\', $namespace);
    }

    public function getTypeScriptName(): string
    {
        $segments = array_merge(
            $this->getNamespaceSegments(),
            [$this->name]
        );

        return implode('.', $segments);
    }

    public function replaceSymbol(string $class, string $replacement): void
    {
        $this->missingSymbols->remove($class);

        $this->transformed = str_replace(
            "{%{$class}%}",
            $replacement,
            $this->transformed
        );
    }
}
