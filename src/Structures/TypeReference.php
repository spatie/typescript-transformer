<?php

namespace Spatie\TypeScriptTransformer\Structures;

class TypeReference
{
    public static function fromFqcn(string $fqcn): self
    {
        $segments = explode('\\', ltrim($fqcn, '\\'));

        return new self(
            $segments[count($segments) - 1],
            array_slice($segments, 0, count($segments) - 1)
        );
    }

    public function __construct(
        public string $name,
        public array $namespaceSegments,
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
}
