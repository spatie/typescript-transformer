<?php

namespace Spatie\TypeScriptTransformer\Collections;

use ArrayIterator;
use Generator;
use IteratorAggregate;
use Spatie\TypeScriptTransformer\References\FilesystemReference;
use Spatie\TypeScriptTransformer\References\Reference;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Traversable;

/**
 * @implements IteratorAggregate<Transformed>
 */
class TransformedCollection implements IteratorAggregate
{
    /** @var array<string, Transformed> */
    protected array $items = [];

    /** @var array<string, Transformed> */
    protected array $fileMapping = [];

    public function __construct(
        array $items = [],
    ) {
        $this->add(...$items);
    }

    public function add(Transformed ...$transformed): self
    {
        foreach ($transformed as $item) {
            $this->items[$item->reference->getKey()] = $item;

            if ($item->reference instanceof FilesystemReference) {
                $this->fileMapping[$this->cleanupFilePath($item->reference->getFilesystemOriginPath())] = $item;
            }
        }

        return $this;
    }

    public function has(Reference|string $reference): bool
    {
        return array_key_exists(is_string($reference) ? $reference : $reference->getKey(), $this->items);
    }

    public function get(Reference|string $reference): ?Transformed
    {
        return $this->items[is_string($reference) ? $reference : $reference->getKey()] ?? null;
    }

    public function remove(Reference|string $reference): void
    {
        $transformed = $this->get($reference);

        if ($transformed === null) {
            return;
        }

        foreach (array_unique($transformed->referencedBy) as $referencedBy) {
            $referencedBy = $this->get($referencedBy);

            $referencedBy->markReferenceMissing($transformed);
            $referencedBy->markAsChanged();
        }

        unset($this->items[$transformed->reference->getKey()]);

        if ($transformed->reference instanceof FilesystemReference) {
            $path = $this->cleanupFilePath($transformed->reference->getFilesystemOriginPath());

            unset($this->fileMapping[$path]);
        }
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function all(): array
    {
        return $this->items;
    }

    public function onlyChanged(): Generator
    {
        foreach ($this->items as $item) {
            if ($item->changed) {
                yield $item;
            }
        }
    }

    public function findTransformedByFile(string $path): ?Transformed
    {
        $path = $this->cleanupFilePath($path);

        return $this->fileMapping[$path] ?? null;
    }

    public function findTransformedByDirectory(string $path): Generator
    {
        $path = $this->cleanupFilePath($path);

        foreach ($this->fileMapping as $transformedPath => $transformed) {
            if (str_starts_with($transformedPath, $path)) {
                yield $transformed;
            }
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

    protected function cleanupFilePath(string $path): string
    {
        return realpath($path);
    }
}
