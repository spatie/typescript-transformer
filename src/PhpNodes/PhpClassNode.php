<?php

namespace Spatie\TypeScriptTransformer\PhpNodes;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionEnum;
use ReflectionMethod;
use ReflectionProperty;
use Roave\BetterReflection\Reflection\ReflectionAttribute as RoaveReflectionAttribute;
use Roave\BetterReflection\Reflection\ReflectionClass as RoaveReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionEnum as RoaveReflectionEnum;
use Roave\BetterReflection\Reflection\ReflectionMethod as RoaveReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionProperty as RoaveReflectionProperty;

class PhpClassNode
{
    public function __construct(
        public readonly ReflectionClass|RoaveReflectionClass $reflection,
    ) {
    }

    public static function fromClassString(string $classString): self
    {
        return self::fromReflection(new ReflectionClass($classString));
    }

    public static function fromReflection(ReflectionClass|RoaveReflectionClass $reflection): self
    {
        if ($reflection instanceof RoaveReflectionEnum) {
            return new PhpEnumNode($reflection);
        }

        if ($reflection->isEnum()) {
            return new PhpEnumNode(new ReflectionEnum($reflection->getName()));
        }

        return new self($reflection);
    }

    /**
     * @return array<PhpAttributeNode>
     */
    public function getAttributes(?string $name = null): array
    {
        $attributes = match (true) {
            $this->reflection instanceof ReflectionClass => $this->reflection->getAttributes($name),
            $name === null => $this->reflection->getAttributes(),
            default => $this->reflection->getAttributesByInstance($name),
        };

        return array_map(
            fn (ReflectionAttribute|RoaveReflectionAttribute $attribute) => new PhpAttributeNode($attribute),
            $attributes,
        );
    }

    public function getProperties(?int $filter = null): array
    {
        return array_map(
            fn (ReflectionProperty|RoaveReflectionProperty $property) => new PhpPropertyNode($property),
            $this->reflection->getProperties($filter),
        );
    }

    public function getMethods(?int $filter = null): array
    {
        return array_map(
            fn (ReflectionMethod|RoaveReflectionMethod $method) => new PhpMethodNode($method),
            $this->reflection->getMethods($filter),
        );
    }

    public function getShortName(): string
    {
        return $this->reflection->getShortName();
    }

    public function getName(): string
    {
        return $this->reflection->getName();
    }

    public function getNamespaceName(): string
    {
        return $this->reflection->getNamespaceName();
    }

    public function getFileName(): string
    {
        return $this->reflection->getFileName();
    }

    public function inNamespace(): bool
    {
        return $this->reflection->inNamespace();
    }

    public function implementsInterface(string $interface): bool
    {
        return $this->reflection->implementsInterface($interface);
    }

    public function isEnum(): bool
    {
        return $this->reflection->isEnum();
    }

    public function isAbstract(): bool
    {
        return $this->reflection->isAbstract();
    }

    public function isFinal(): bool
    {
        return $this->reflection->isFinal();
    }

    public function isInterface(): bool
    {
        return $this->reflection->isInterface();
    }

    public function isReadonly(): bool
    {
        return $this->reflection->isReadonly();
    }

    public function getDocComment(): ?string
    {
        return $this->reflection->getDocComment() ?: null;
    }

    public function hasMethod(string $name): bool
    {
        return $this->reflection->hasMethod($name);
    }

    public function getMethod(string $name): ?PhpMethodNode
    {
        $method = $this->reflection->getMethod($name);

        return $method ? new PhpMethodNode($method) : null;
    }
}
