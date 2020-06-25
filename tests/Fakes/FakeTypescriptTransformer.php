<?php

namespace Spatie\TypescriptTransformer\Tests\Fakes;

use MyCLabs\Enum\Enum;
use ReflectionClass;
use Spatie\TypescriptTransformer\Structures\Type;
use Spatie\TypescriptTransformer\Transformers\Transformer;

class FakeTypescriptTransformer implements Transformer
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

    public function transform(ReflectionClass $class, string $name): Type
    {
        return FakeType::fake($name)
            ->withReflection($class)
            ->withTransformed($this->transformed);
    }
}
