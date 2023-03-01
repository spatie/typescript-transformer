<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes;

use Exception;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\Structures\TypeReferencesCollection;

class FakeTransformedType extends TransformedType
{
    public static function create(ReflectionClass $class, string $name, string $transformed, ?TypeReferencesCollection $typeReferences = null, bool $inline = false, string $keyword = 'type', bool $trailingSemicolon = true): TransformedType
    {
        throw new Exception("Fake type");
    }

    public static function createInline(ReflectionClass $class, string $transformed, ?TypeReferencesCollection $typeReferences = null): TransformedType
    {
        throw new Exception("Fake type");
    }

    public static function fake(?string $name = null): self
    {
        $name ??= 'FakeType';

        return new self(
            FakeReflectionClass::create()->withName($name),
            $name,
            'fake-transformed',
            new TypeReferencesCollection(),
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

    public function withTypeReferences(array $typeReferences): self
    {
        foreach ($typeReferences as $typeReference) {
            $this->typeReferences->add($typeReference);
        }

        return $this;
    }

    public function isInline(bool $isInline = true): self
    {
        $this->isInline = $isInline;

        return $this;
    }
}
