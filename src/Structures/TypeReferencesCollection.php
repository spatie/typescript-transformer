<?php

namespace Spatie\TypeScriptTransformer\Structures;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<\Spatie\TypeScriptTransformer\Structures\TypeReference>
 */
class TypeReferencesCollection implements Countable, IteratorAggregate
{
    /** @var array<string, \Spatie\TypeScriptTransformer\Structures\TypeReference> */
    protected array $references = [];

    public function remove(string|TypeReference $fqcn): void
    {
        $reference = $fqcn instanceof TypeReference
            ? $fqcn->getFqcn()
            : $fqcn;

        if (in_array($reference, $this->references)) {
            unset($this->references[array_search($reference, $this->references)]);
        }
    }

    public function has(string|TypeReference $fqcn): bool
    {
        $reference = $fqcn instanceof TypeReference
            ? $fqcn->getFqcn()
            : $fqcn;

        return array_key_exists($reference, $this->references);
    }

    public function add(string|TypeReference $fqcn): TypeReference
    {
        $reference = $fqcn instanceof TypeReference
            ? $fqcn
            : TypeReference::fromFqcn($fqcn);

        $fqcn = $reference->getFqcn();

        if (! $this->has($fqcn)) {
            $this->references[$fqcn] = $reference;
        }

        return $this->references[$fqcn];
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->references);
    }

    public function count(): int
    {
        return count($this->references);
    }
}
