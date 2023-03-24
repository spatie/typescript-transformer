<?php

namespace Spatie\TypeScriptTransformer\Structures;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Spatie\TypeScriptTransformer\Structures\Transformed\Transformed;

/**
 * @implements IteratorAggregate<\Spatie\TypeScriptTransformer\Structures\Transformed\Transformed>
 */
class TypesCollection implements Countable, IteratorAggregate
{
    protected array $types = [];

    public static function create(): self
    {
        return new self();
    }

    public function add(Transformed $type): void
    {
        $class ??= $type->name->getFqcn();

        $class = $class instanceof Transformed
            ? $class->name->getFqcn()
            : $class;

        $this->types[$class] = $type;
    }

    public function has(string $class): bool
    {
        return array_key_exists($class, $this->types);
    }

    public function get(string $class): ?Transformed
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
