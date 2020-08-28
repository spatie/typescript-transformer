<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes;

use MyCLabs\Enum\Enum;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\Transformers\Transformer;

class FakeTypeScriptTransformer implements Transformer
{
    private string $transformed = 'fake';

    public static function create(): self
    {
        return new self();
    }

    public function canTransform(ReflectionClass $class): bool
    {
        return $class->isSubclassOf(Enum::class);
    }

    public function transform(ReflectionClass $class, string $name): TransformedType
    {
        return FakeTransformedType::fake($name)
            ->withReflection($class)
            ->withTransformed($this->transformed);
    }
}
