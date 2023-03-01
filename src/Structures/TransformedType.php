<?php

namespace Spatie\TypeScriptTransformer\Structures;

use ReflectionClass;

class TransformedType
{
    public TypeReference $referencing;

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
        $this->referencing = TypeReference::fromFqcn($this->reflection->getName());
    }

    public function getNamespaceSegments(): array
    {
        if ($this->isInline === true) {
            return [];
        }

        return $this->referencing->namespaceSegments;
    }

    public function getTypeScriptName($fullyQualified = true): string
    {
        if (! $fullyQualified) {
            return $this->name ?? '';
        }

        return $this->referencing->getTypeScriptFqcn();
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
