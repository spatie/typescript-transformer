<?php

namespace Spatie\TypeScriptTransformer\Structures;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Spatie\TypeScriptTransformer\Exceptions\SymbolAlreadyExists;

/**
 * @implements IteratorAggregate<\Spatie\TypeScriptTransformer\Structures\TransformedType>
 */
class TypesCollection implements Countable, IteratorAggregate
{
    protected array $types = [];

    protected array $structure = [];

    public static function create(): self
    {
        return new self();
    }

    public function add(TransformedType $type): void
    {
        $class ??= $type->reflection->getName();

        $class = $class instanceof TransformedType
            ? $class->reflection->getName()
            : $class;

        $this->types[$class] = $type;
    }

    public function has(string $class): bool
    {
        return array_key_exists($class, $this->types);
    }

    public function get(string $class): ?TransformedType
    {
        return $this->types[$class] ?? null;
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->types);
    }

    public function count(): int
    {
        return count($this->types);
    }
}
