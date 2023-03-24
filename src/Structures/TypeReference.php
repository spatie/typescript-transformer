<?php

namespace Spatie\TypeScriptTransformer\Structures;

use Spatie\TypeScriptTransformer\Structures\Transformed\Transformed;

class TypeReference
{
    public static function fromFqcn(string $fqcn, ?string $alternativeName = null): self
    {
        $segments = explode('\\', ltrim($fqcn, '\\'));

        return new self(
            $segments[count($segments) - 1],
            array_slice($segments, 0, count($segments) - 1),
            $alternativeName
        );
    }

    public function __construct(
        public string $name,
        public array $namespaceSegments,
        public ?string $alternativeName = null,
        public ?Transformed $referenced = null,
    ) {
    }

    public function replaceSymbol(): string
    {
        return "{%{$this->getFqcn()}%}";
    }

    public function getFqcn(): string
    {
        if (empty($this->namespaceSegments)) {
            return $this->name;
        }

        return implode('\\', $this->namespaceSegments) . "\\{$this->name}";
    }

    public function getTypeScriptName(): string
    {
        return $this->alternativeName ?? $this->name;
    }

    public function getTypeScriptFqcn(): string
    {
        return implode('.', [...$this->namespaceSegments, $this->name]);
    }
}
