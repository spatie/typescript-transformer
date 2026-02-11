<?php

namespace Spatie\TypeScriptTransformer\Data;

class WritingContext
{
    /** @var array<string> */
    private array $currentNamespaceSegments = [];

    /** @param array<string, string> $resolvedReferenceMap Maps the reference keys to their resolved names(either a symbol or namespace dotted symbol) */
    public function __construct(
        public array $resolvedReferenceMap,
    ) {
    }

    public function pushNamespace(string $name): void
    {
        $this->currentNamespaceSegments[] = $name;
    }

    public function popNamespace(): void
    {
        array_pop($this->currentNamespaceSegments);
    }

    public function resolveReference(string $referenceKey): string
    {
        $name = $this->resolvedReferenceMap[$referenceKey] ?? 'undefined';

        if (empty($this->currentNamespaceSegments) || ! str_contains($name, '.')) {
            return $name;
        }

        $segments = explode('.', $name);

        while (count($segments) > 1) {
            $shadowed = false;

            for ($i = count($this->currentNamespaceSegments) - 1; $i >= 1; $i--) {
                if ($this->currentNamespaceSegments[$i] === $segments[0]) {
                    $shadowed = true;

                    break;
                }
            }

            if (! $shadowed) {
                break;
            }

            array_shift($segments);
        }

        return implode('.', $segments);
    }
}
