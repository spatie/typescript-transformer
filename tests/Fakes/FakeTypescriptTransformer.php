<?php

namespace Spatie\TypescriptTransformer\Tests\Fakes;

use MyCLabs\Enum\Enum;
use ReflectionClass;
use Spatie\TypescriptTransformer\Structures\TransformedType;
use Spatie\TypescriptTransformer\Transformers\Transformer;

class FakeTypescriptTransformer extends Transformer
{
    private string $transformed = 'fake';

    public static function create(): self
    {
        return new self();
    }

    public function withTransformed(string $transformed): self
    {
        $this->transformed = $transformed;

        return $this;
    }

    public function withMissingSymbols(array $missingSymbols): self
    {
        $this->missingSymbols = $missingSymbols;

        return $this;
    }

    public function canTransform(ReflectionClass $class): bool
    {
        return $class->isSubclassOf(Enum::class);
    }

    public function transform(ReflectionClass $class, string $name): string
    {
        return $this->transformed;
    }
}
