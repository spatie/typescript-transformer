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

    public string $keyword;

    public array $imports = [];

    public static function create(
        ReflectionClass $class,
        string $name,
        string $transformed,
        ?MissingSymbolsCollection $missingSymbols = null,
        bool $inline = false,
        string $keyword = 'type'
    ): self {
        return new self($class, $name, $transformed, $missingSymbols ?? new MissingSymbolsCollection(), $inline, $keyword);
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
        bool $isInline,
        string $keyword = 'type'
    ) {
        $this->reflection = $class;
        $this->name = $name;
        $this->transformed = $transformed;
        $this->missingSymbols = $missingSymbols;
        $this->isInline = $isInline;
        $this->keyword = $keyword;
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

        $this->transformed = str_replace(
            "{%{$class}%}",
            $replacement,
            $this->transformed
        );
    }

    public function addImport(string $fullQualifiedName): void
    {
        if (in_array(strtolower($fullQualifiedName), ['any', 'array', 'boolean', 'never', 'null', 'number', 'string', 'object'])) {
            return;
        }

        $replacementSegments = explode('\\', $fullQualifiedName);
        $importName = array_pop($replacementSegments);
        $thisSegments = $this->getNamespaceSegments();

        while(count($thisSegments) && count($replacementSegments) && ($thisSegments[0] === $replacementSegments[0]))
        {
            array_shift($thisSegments);
            array_shift($replacementSegments);
        }

        $relativePath = str_pad('', count($thisSegments) * 2, '../').implode('/', $replacementSegments);

        if (count($thisSegments)) {
            // path => module name
            $this->imports[$relativePath] = $importName;
        }

        $this->imports["./{$importName}"] = $importName;
    }

    public function toString(): string
    {
        return match ($this->keyword) {
            'enum' => "enum {$this->name} { {$this->transformed} }",
            default => "type {$this->name} = {$this->transformed}",
        };
    }
}
