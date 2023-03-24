<?php

namespace Spatie\TypeScriptTransformer\Tests\Factories;

use ReflectionClass;
use Spatie\TypeScriptTransformer\Structures\OldTransformedType;
use Spatie\TypeScriptTransformer\Structures\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Structures\TypeReference;
use Spatie\TypeScriptTransformer\Structures\TypeReferencesCollection;
use Spatie\TypeScriptTransformer\Structures\TypeScript\TypeScriptRaw;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeReflectionClass;

class TransformedFactory
{
    public static function create(?string $name = null): self
    {
        return new self(TypeReference::fromFqcn($name ?? 'FakeType'));
    }

    /**
     * @param array<string|TypeReference> $typeReferences
     */
    protected function __construct(
        protected TypeReference $name,
        protected string $transformed = 'fake-transformed',
        protected array $typeReferences = [],
        protected bool $isInline = false,
    ) {
    }

    public function build(): Transformed
    {
        $typeReferences = new TypeReferencesCollection();

        foreach ($this->typeReferences as $typeReference) {
            $typeReferences->add($typeReference);
        }

        return new Transformed(
            $this->name,
            new TypeScriptRaw($this->transformed),
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
