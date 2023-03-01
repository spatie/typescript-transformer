<?php

namespace Spatie\TypeScriptTransformer\Structures;

use ReflectionClass;

class TransformedType
{
    public static function create(
        ReflectionClass $class,
        string $name,
        string $transformed,
        ?TypeReferencesCollection $typeReferences = null,
        bool $inline = false,
        string $keyword = 'type',
        bool $trailingSemicolon = true,
    ): self {
        return new self(
            reflection: $class,
            name: $name,
            transformed: $transformed,
            typeReferences: $typeReferences ?? new TypeReferencesCollection(),
            isInline: $inline,
            keyword: $keyword,
            trailingSemicolon: $trailingSemicolon
        );
    }

    public static function createInline(
        ReflectionClass $class,
        string $transformed,
        ?TypeReferencesCollection $typeReferences = null
    ): self {
        return new self(
            reflection: $class,
            name: null,
            transformed: $transformed,
            typeReferences: $typeReferences ?? new TypeReferencesCollection(),
            isInline: true
        );
    }

    public function __construct(
        public ReflectionClass $reflection,
        public ?string $name,
        public string $transformed,
        public TypeReferencesCollection $typeReferences,
        public bool $isInline,
        public string $keyword = 'type',
        public bool $trailingSemicolon = true,
    ) {
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

    public function replaceTypeReference(TypeReference $typeReference, string $replacement): void
    {
        $this->typeReferences->remove($typeReference);

        $this->transformed = str_replace(
            $typeReference->replaceSymbol(),
            $replacement,
            $this->transformed
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
