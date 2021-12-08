<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes;

use ReflectionType;
use ReflectionUnionType;

trait FakedReflection
{
    private null | ReflectionType | FakeReflectionType $type = null;

    private ?string $entityName = null;

    private string $docComment = '';

    public function __construct()
    {
    }

    public static function create(): static
    {
        return new static();
    }

    public function withDocComment(string $docComment): self
    {
        $this->docComment = $docComment;

        return $this;
    }

    public function withName(string $name): self
    {
        $this->entityName = $name;

        return $this;
    }

    public function withType(FakeReflectionType | ReflectionType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDocComment(): string|false
    {
        if ($this->docComment === '') {
            return false;
        }

        return $this->docComment;
    }

    public function getName(): string
    {
        if ($this->entityName === null) {
            return '';
        }

        return $this->entityName;
    }

    public function getType(): null | ReflectionType | ReflectionUnionType | FakeReflectionType
    {
        if ($this->type === null) {
            return null;
        }

        return $this->type;
    }

    public function getAttributes(?string $name = null, int $flags = 0): array
    {
        return [];
    }
}
