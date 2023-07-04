<?php

namespace Spatie\TypeScriptTransformer\Support;

use ArrayIterator;
use IteratorAggregate;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Traversable;

/**
 * @implements IteratorAggregate<Transformed>
 */
class TransformedCollection implements IteratorAggregate
{
    /**
     * @param array<Transformed> $items
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
}
