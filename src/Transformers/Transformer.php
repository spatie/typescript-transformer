<?php

namespace Spatie\TypescriptTransformer\Transformers;

use ReflectionClass;

abstract class Transformer
{
    protected array $missingSymbols = [];

    abstract public function canTransform(ReflectionClass $class): bool;

    abstract protected function transform(ReflectionClass $class, string $name): string;

    public function isInline(): bool
    {
        return false;
    }

    public function execute(ReflectionClass $class, string $name)
    {
        $this->missingSymbols = [];

        return [
            'transformed' => $this->transform($class, $name),
            'missingSymbols' => $this->missingSymbols,
            'isInline' => $this->isInline(),
        ];
    }

    protected function addMissingSymbol(string $symbol): string
    {
        $symbol = ltrim($symbol, '\\');

        if (! in_array($symbol, $this->missingSymbols)) {
            $this->missingSymbols[] = $symbol;
        }

        return "{%{$symbol}%}";
    }
}
