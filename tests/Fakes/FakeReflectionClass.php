<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes;

use ReflectionClass;

class FakeReflectionClass extends ReflectionClass
{
    use FakedReflection;

    private ?string $withNamespace = null;

    public function __construct()
    {
        parent::__construct(new class{});
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

    public function getNamespaceName()
    {
        return $this->withNamespace ?? parent::getNamespaceName();
    }

    public function getName()
    {
        $name = $this->entityName ?? parent::getShortName();

        return empty($this->getNamespaceName())
            ? $name
            : "{$this->getNamespaceName()}\\{$name}";
    }
}
