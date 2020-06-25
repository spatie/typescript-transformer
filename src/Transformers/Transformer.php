<?php

namespace Spatie\TypescriptTransformer\Transformers;

use ReflectionClass;
use Spatie\TypescriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypescriptTransformer\Structures\Type;

interface Transformer
{
    public function canTransform(ReflectionClass $class): bool;

    public function transform(ReflectionClass $class, string $name): Type;
}
