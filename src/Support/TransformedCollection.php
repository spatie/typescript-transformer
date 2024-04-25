<?php

namespace Spatie\TypeScriptTransformer\Support;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Traversable;

/**
 * @implements IteratorAggregate<Transformed>
 */
class TransformedCollection implements IteratorAggregate, ArrayAccess
{
    /**
     * @param  array<Transformed>  $items
     */
    public function __construct(
        protected array $items = [],
    ) {
    }

    public function add(Transformed ...$transformed): self
    {
        array_push($this->items, ...$transformed);

        return $this;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet(mixed $offset): Transformed
    {
        return $this->items[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->items[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    public function all(): array
    {
        return $this->items;
    }
}
