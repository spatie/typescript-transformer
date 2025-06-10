<?php

namespace Spatie\TypeScriptTransformer\Structures;

use ReflectionClass;
use Spatie\TypeScriptTransformer\Compactors\Compactor;

class TransformedType
{
    public ReflectionClass $reflection;

    public ?string $name = null;

    public TranspilationResult $transformed;

    public MissingSymbolsCollection $missingSymbols;

    public Compactor $compactor;

    public bool $isInline;

    public string $keyword;

    public bool $trailingSemicolon;

    public static function create(
        ReflectionClass $class,
        string $name,
        TranspilationResult $transformed,
        Compactor $compactor,
        ?MissingSymbolsCollection $missingSymbols = null,
        bool $inline = false,
        string $keyword = 'type',
        bool $trailingSemicolon = true,
    ): self {
        return new self($class, $compactor->removeSuffix($name), $transformed, $compactor, $missingSymbols ?? new MissingSymbolsCollection(), $inline, $keyword, $trailingSemicolon);
    }

    public static function createInline(
        ReflectionClass $class,
        TranspilationResult $transformed,
        Compactor $compactor,
        ?MissingSymbolsCollection $missingSymbols = null
    ): self {
        return new self($class, null, $transformed, $compactor, $missingSymbols ?? new MissingSymbolsCollection(), true);
    }

    public function __construct(
        ReflectionClass $class,
        ?string $name,
        TranspilationResult $transformed,
        Compactor $compactor,
        MissingSymbolsCollection $missingSymbols,
        bool $isInline,
        string $keyword = 'type',
        bool $trailingSemicolon = true,
    ) {
        $this->reflection = $class;
        $this->name = $name;
        $this->transformed = $transformed;
        $this->missingSymbols = $missingSymbols;
        $this->compactor = $compactor;
        $this->isInline = $isInline;
        $this->keyword = $keyword;
        $this->trailingSemicolon = $trailingSemicolon;
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

        $namespace = $this->compactor->removePrefix($namespace);

        if ($namespace === '') {
            return [];
        }

        return explode('\\', $namespace);
    }

    public function getTypeScriptName($fullyQualified = true): string
    {
        if (! $fullyQualified) {
            return $this->name ?? '';
        }

        $segments = array_merge(
            $this->getNamespaceSegments(),
            [$this->name]
        );

        return implode('.', $segments);
    }

    public function replaceSymbol(string $class, string $replacement): void
    {
        $this->missingSymbols->remove($class);

        $this->transformed->typescript = str_replace(
            "{%{$class}%}",
            $replacement,
            $this->transformed->typescript
        );
    }

    public function toString(): string
    {
        $output = match ($this->keyword) {
            'enum' => "enum {$this->name} { {$this->transformed} }",
            'interface' => "interface {$this->name} {$this->transformed}",
            default => "type {$this->name} = {$this->transformed}",
        };

        return $output . ($this->trailingSemicolon ? ';' : '');
    }
}
