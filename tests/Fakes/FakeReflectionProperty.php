<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes;

use ReflectionProperty;

class FakeReflectionProperty extends ReflectionProperty
{
    private ?FakeReflectionType $type = null;

    private ?string $docComment = null;

    public static function create(): self
    {
        return new self();
    }

    public function __construct()
    {
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
