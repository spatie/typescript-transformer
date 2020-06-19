<?php

namespace Spatie\TypescriptTransformer\Tests\Fakes;

use ReflectionClass;
use Spatie\TypescriptTransformer\Structures\Type;

class FakeType extends Type
{
    public static function create(?string $name = null)
    {
        $name ??= 'FakeType';

        return new self(
            FakeReflection::create()->withName($name),
            $name,
            'fake-transformed',
            [],
            false
        );
    }

    public function withReflection(ReflectionClass $reflection): self
    {
        $this->reflection = $reflection;

        return $this;
    }

    public function withNamespace(string $namespace): self
    {
        $this->reflection->withNamespace($namespace);

        return $this;
    }

    public function withoutNamespace(): self
    {
        $this->reflection->withoutNamespace();

        return $this;
    }

    public function withTransformed(string $transformed): self
    {
        $this->transformed = $transformed;

        return $this;
    }

    public function withMissingSymbols(array $missingSymbols): self
    {
        $this->missingSymbols = $missingSymbols;

        if (! empty($this->missingSymbols)) {
            $this->isCompletelyReplaced = false;
        }

        return $this;
    }

    public function isInline(bool $isInline = true): self
    {
        $this->isInline = $isInline;

        return $this;
    }
}
