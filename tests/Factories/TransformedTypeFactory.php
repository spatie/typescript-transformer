<?php

namespace Spatie\TypeScriptTransformer\Tests\Factories;

use ReflectionClass;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\Structures\TypeReference;
use Spatie\TypeScriptTransformer\Structures\TypeReferencesCollection;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeReflectionClass;

class TransformedTypeFactory
{
    public static function create(?string $name = null): self
    {
        return new self(TypeReference::fromFqcn($name ?? 'FakeType'));
    }

    /**
     * @param array<string|TypeReference> $typeReferences
     */
    protected function __construct(
        protected TypeReference $referencing,
        protected string $transformed = 'fake-transformed',
        protected ?ReflectionClass $reflection = null,
        protected array $typeReferences = [],
        protected bool $isInline = false,
    ) {
    }

    public function build(): TransformedType
    {
        $reflection = FakeReflectionClass::create()->withName($this->referencing->name);

        if (! empty($this->referencing->namespaceSegments)) {
            $reflection->withNamespace(implode('\\', $this->referencing->namespaceSegments));
        }

        $typeReferences = new TypeReferencesCollection();

        foreach ($this->typeReferences as $typeReference) {
            $typeReferences->add($typeReference);
        }

        return new TransformedType(
            $this->reflection ?? $reflection,
            $this->referencing->name,
            $this->transformed,
            $typeReferences,
            $this->isInline,
        );
    }

    public function withTransformed(string $transformed): self
    {
        $clone = clone $this;

        $clone->transformed = $transformed;

        return $clone;
    }

    public function withReflection(ReflectionClass $reflection): self
    {
        $clone = clone $this;

        $clone->reflection = $reflection;

        return $clone;
    }

    public function withTypeReferences(string|TypeReference ...$typeReferences): self
    {
        $clone = clone $this;

        array_push($clone->typeReferences, ...$typeReferences);

        return $clone;
    }

    public function isInline(bool $isInline = true): self
    {
        $clone = clone $this;

        $clone->isInline = $isInline;

        return $clone;
    }
}
