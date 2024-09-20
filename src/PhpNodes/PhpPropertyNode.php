<?php

namespace Spatie\TypeScriptTransformer\PhpNodes;

use ReflectionAttribute;
use ReflectionProperty;
use Roave\BetterReflection\Reflection\ReflectionAttribute as RoaveReflectionAttribute;
use Roave\BetterReflection\Reflection\ReflectionProperty as RoaveReflectionProperty;

class PhpPropertyNode
{
    public function __construct(
        public readonly ReflectionProperty|RoaveReflectionProperty $reflection
    ) {
    }

    public function getName(): string
    {
        return $this->reflection->getName();
    }

    public function getDeclaringClass(): PhpClassNode
    {
        return new PhpClassNode($this->reflection->getDeclaringClass());
    }

    /**
     * @return array<PhpAttributeNode>
     */
    public function getAttributes(?string $name = null): array
    {
        $attributes = match (true) {
            $this->reflection instanceof ReflectionProperty => $this->reflection->getAttributes($name),
            $name === null => $this->reflection->getAttributes(),
            default => $this->reflection->getAttributesByInstance($name),
        };

        return array_map(
            fn (ReflectionAttribute|RoaveReflectionAttribute $attribute) => new PhpAttributeNode($attribute),
            $attributes,
        );
    }

    public function isStatic(): bool
    {
        return $this->reflection->isStatic();
    }

    public function hasType(): bool
    {
        return $this->reflection->hasType();
    }

    public function getType(): ?PhpTypeNode
    {
        $type = $this->reflection->getType();

        if ($type === null) {
            return null;
        }

        return PhpTypeNode::fromReflection($type);
    }

    public function isReadonly(): bool
    {
        return $this->reflection->isReadonly();
    }

    public function getDocComment(): ?string
    {
        return $this->reflection->getDocComment() ?: null;
    }
}
