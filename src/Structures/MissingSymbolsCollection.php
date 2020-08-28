<?php

namespace Spatie\TypeScriptTransformer\Structures;

class MissingSymbolsCollection
{
    protected array $missingSymbols = [];

    public function all(): array
    {
        return $this->missingSymbols;
    }

    public function remove(string $symbol)
    {
        if (in_array($symbol, $this->missingSymbols)) {
            unset($this->missingSymbols[array_search($symbol, $this->missingSymbols)]);
        }
    }

    public function isEmpty(): bool
    {
        return empty($this->missingSymbols);
    }

    public function add(string $symbol): string
    {
        $symbol = ltrim($symbol, '\\');

        if (! in_array($symbol, $this->missingSymbols)) {
            $this->missingSymbols[] = $symbol;
        }

        return "{%{$symbol}%}";
    }
}
