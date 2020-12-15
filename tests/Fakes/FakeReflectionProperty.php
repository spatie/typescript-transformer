<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes;

use ReflectionProperty;
use ReflectionType;

class FakeReflectionProperty extends ReflectionProperty
{
    private null|FakeReflectionType|FakeReflectionUnionType $type = null;

    private ?string $docComment = null;

    public static function create(): self
    {
        return new self();
    }

    public function __construct()
    {
    }

    public function withType(FakeReflectionType|FakeReflectionUnionType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function withDocComment(string $docComment): self
    {
        $this->docComment = $docComment;

        return $this;
    }

    public function getType(): null|FakeReflectionType|FakeReflectionUnionType
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

    public function getModifiers()
    {
        return 0;
    }

    public function getAttributes(?string $name = null, int $flags = 0): array
    {
        return [];
    }
}
