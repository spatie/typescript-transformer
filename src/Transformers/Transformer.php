<?php

namespace Spatie\TypescriptTransformer\Transformers;

use ReflectionClass;

abstract class Transformer
{
    protected array $missingSymbols = [];

    abstract public function canTransform(ReflectionClass $class): bool;

    public function execute(ReflectionClass $class, string $name)
    {
        $this->missingSymbols = [];

        return [
            'transformed' => $this->transform($class, $name),
            'missingSymbols' => $this->missingSymbols,
        ];
    }

    abstract protected function transform(ReflectionClass $class, string $name): string;
}
