<?php

namespace Spatie\TypescriptTransformer\Tests\FakeClasses;

use ReflectionClass;

class FakeReflection extends ReflectionClass
{
    private ?string $withNamespace = null;

    private ?string $withName = null;

    public static function create(): self
    {
        return new self(new class {
        });
    }

    public function withNamespace(string $namespace): self
    {
        $this->withNamespace = $namespace;

        return $this;
    }

    public function withoutNamespace(): self
    {
        $this->withNamespace = '';

        return $this;
    }

    public function withName(string $name): self
    {
        $this->withName = $name;

        return $this;
    }

    public function getNamespaceName()
    {
        return $this->withNamespace ?? parent::getNamespaceName();
    }

    public function getName()
    {
        $name = $this->withName ?? parent::getShortName();

        return empty($this->getNamespaceName())
            ? $name
            : "{$this->getNamespaceName()}\\{$name}";
    }
}
