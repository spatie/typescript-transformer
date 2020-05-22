<?php

namespace Spatie\TypescriptTransformer\Tests\FakeClasses;

use MyCLabs\Enum\Enum;
use ReflectionClass;
use Spatie\TypescriptTransformer\Transformers\Transformer;

class FakeTypescriptTransformer implements Transformer
{
    public function canTransform(ReflectionClass $class): bool
    {
        return $class->isSubclassOf(Enum::class);
    }

    public function transform(ReflectionClass $class, string $name): string
    {
        return 'fake';
    }
}
