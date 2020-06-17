<?php

namespace Spatie\TypescriptTransformer\Transformers;

use ReflectionClass;
use Spatie\TypescriptTransformer\Structures\TransformedType;

abstract class Transformer
{
    protected array $missingSymbols = [];

    public abstract function canTransform(ReflectionClass $class): bool;

    public function execute(ReflectionClass $class, string $name)
    {
        $this->missingSymbols = [];

        return [
            'transformed' => $this->transform($class, $name),
            'missingSymbols' => $this->missingSymbols,
        ];
    }

    protected abstract function transform(ReflectionClass $class, string $name): string;


}
