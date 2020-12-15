<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes;

use ReflectionMethod;

class FakeReflectionMethod extends ReflectionMethod
{
    private string $methodName;

    private ?FakeReflectionType $type = null;

    private ?string $docComment = null;

    public function __construct(string $name)
    {
        $this->methodName = $name;
    }

    public static function create(string $name): self
    {
        return new self($name);
    }

    public function getName()
    {
        return $this->methodName;
    }


    public function withType(FakeReflectionType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function withDocComment(string $docComment): self
    {
        $this->docComment = $docComment;

        return $this;
    }

    public function getType(): ?FakeReflectionType
    {
        return $this->type;
    }

    public function getDocComment()
    {
        if ($this->docComment === null) {
            return false;
        }

        return $this->docComment;
    }
}
