<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes;

use Exception;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\Structures\TransformedType;

class FakeTransformedType extends TransformedType
{
    public static function create(ReflectionClass $class, string $name, string $transformed, ?MissingSymbolsCollection $missingSymbols = null, bool $inline = false): TransformedType
    {
        throw new Exception("Fake type");
    }

    public static function createInline(ReflectionClass $class, string $transformed, ?MissingSymbolsCollection $missingSymbols = null): TransformedType
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
            new MissingSymbolsCollection(),
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

    public function withMissingSymbols(array $missingSymbols): self
    {
        foreach ($missingSymbols as $missingSymbol) {
            $this->missingSymbols->add($missingSymbol);
        }

        return $this;
    }

    public function isInline(bool $isInline = true): self
    {
        $this->isInline = $isInline;

        return $this;
    }
}
