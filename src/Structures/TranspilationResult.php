<?php

namespace Spatie\TypeScriptTransformer\Structures;

class TranspilationResult
{
    /** @var string[] */
    public readonly array $dependencies;

    public function __construct(
        array $dependencies,
        public string $typescript
    ) {
        $this->dependencies = array_unique($dependencies);
    }

    public static function noDeps(string $typescript): static {
        return new TranspilationResult([], $typescript);
    }

    public static function empty(): static {
        return new TranspilationResult([], '');
    }

    public function __toString(): string {
        return $this->typescript;
    }

}