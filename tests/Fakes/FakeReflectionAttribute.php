<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes;

use ReflectionAttribute;

class FakeReflectionAttribute extends ReflectionAttribute
{
    private string $class;

    private array $arguments = [];

    public function __construct()
    {
    }

    public static function create(): static
    {
        return new self();
    }

    public function withClass(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function withArguments(mixed ...$arguments): self
    {
        $this->arguments = $arguments;

        return $this;
    }

    public function getName(): string
    {
        return $this->class;
    }

    public function getArguments(): array
    {
        parent::getArguments();
    }
}
