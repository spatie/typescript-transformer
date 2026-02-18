<?php

namespace Spatie\TypeScriptTransformer\Collections;

use ArrayIterator;
use Countable;
use Generator;
use IteratorAggregate;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Traversable;

/**
 * @implements IteratorAggregate<PhpClassNode>
 */
class PhpNodeCollection implements IteratorAggregate, Countable
{
    /** @var array<string, PhpClassNode> */
    protected array $items = [];

    /** @var array<string, PhpClassNode> */
    protected array $fileMapping = [];

    public function add(PhpClassNode $node): void
    {
        $fqcn = $node->getName();

        $this->remove($fqcn);

        $this->items[$fqcn] = $node;
        $this->fileMapping[$this->cleanupFilePath($node->getFileName())] = $node;
    }

    public function get(string $fqcn): ?PhpClassNode
    {
        return $this->items[$fqcn] ?? null;
    }

    public function has(string $fqcn): bool
    {
        return array_key_exists($fqcn, $this->items);
    }

    public function remove(string $fqcn): void
    {
        $node = $this->items[$fqcn] ?? null;

        if ($node === null) {
            return;
        }

        $path = $this->cleanupFilePath($node->getFileName());

        unset($this->items[$fqcn], $this->fileMapping[$path]);
    }

    public function findByFile(string $path): ?PhpClassNode
    {
        $path = $this->cleanupFilePath($path);

        return $this->fileMapping[$path] ?? null;
    }

    public function removeByFile(string $path): void
    {
        $node = $this->findByFile($path);

        if ($node === null) {
            return;
        }

        $this->remove($node->getName());
    }

    public function findByDirectory(string $path): Generator
    {
        $path = $this->cleanupFilePath($path);

        foreach ($this->fileMapping as $filePath => $node) {
            if (str_starts_with($filePath, $path)) {
                yield $node;
            }
        }
    }

    public function removeByDirectory(string $path): void
    {
        foreach ($this->findByDirectory($path) as $node) {
            $this->remove($node->getName());
        }
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    /** @return array<string, PhpClassNode> */
    public function all(): array
    {
        return $this->items;
    }

    protected function cleanupFilePath(string $path): string
    {
        return realpath($path) ?: $path;
    }
}
