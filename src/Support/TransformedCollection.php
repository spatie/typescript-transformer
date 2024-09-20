<?php

namespace Spatie\TypeScriptTransformer\Support;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use Spatie\TypeScriptTransformer\References\FilesystemReference;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Traversable;

/**
 * @implements IteratorAggregate<Transformed>
 */
class TransformedCollection implements IteratorAggregate, ArrayAccess
{
    /**
     * @param array<string, Transformed> $items
     * @param array<string, string> $fileMapping
     */
    public function __construct(
        protected array $items = [],
        protected array $fileMapping = [],
    ) {
    }

    public function add(Transformed ...$transformed): self
    {
        foreach ($transformed as $item) {
            $this->items[$item->reference->getKey()] = $item;

            if ($item->reference instanceof FilesystemReference) {
                $this->addTransformedFileReference($item, $item->reference);
            }
        }

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

    public function findTransformedByPath(string $path): ?Transformed
    {
        $segments = explode('/', ltrim(realpath($path), DIRECTORY_SEPARATOR));

        $pointer = $this->files;

        foreach ($segments as $segment) {
            if (! isset($pointer[$segment])) {
                return null;
            }

            $pointer = $pointer[$segment];
        }

        return $pointer;
    }

    public function removeTransformedByPath(string $path): void
    {
        $segments = explode('/', ltrim(realpath($path), DIRECTORY_SEPARATOR));

        $pointer = &$this->files;

        foreach ($segments as $i => $segment) {
            if (! isset($pointer[$segment])) {
                return;
            }

            if ($i === count($segments) - 1) {
                /** @var Transformed $transformed */
                $transformed = $pointer[$segment];

                unset($this->items[$this->resolveIdForTransformed($transformed)]);
                unset($pointer[$segment]);

                break;
            }

            $pointer = &$pointer[$segment];
        }
    }

    public function hasChanges(): bool
    {
        foreach ($this->items as $item) {
            if ($item->changed) {
                return true;
            }
        }

        return false;
    }

    protected function addTransformedFileReference(Transformed $transformed, FilesystemReference $reference): void
    {
        $segments = explode('/', ltrim(realpath($reference->getFilesystemOriginPath()), DIRECTORY_SEPARATOR));

        $pointer = &$this->files;

        foreach ($segments as $i => $segment) {
            if (! isset($pointer[$segment])) {
                $pointer[$segment] = [];
            }

            if ($i === count($segments) - 1) {
                $pointer[$segment] = $transformed;
                $this->items[$this->resolveIdForTransformed($transformed)] = $transformed;

                break;
            }

            $pointer = &$pointer[$segment];
        }
    }

    protected function resolveIdForTransformed(Transformed $transformed): string
    {
        if ($transformed->reference instanceof FilesystemReference) {
            return $transformed->reference->getFilesystemOriginPath();
        }

        return spl_object_id($transformed);
    }
}
