<?php

namespace Spatie\TypeScriptTransformer\Collections;

use ArrayIterator;
use IteratorAggregate;
use Spatie\TypeScriptTransformer\Writers\Writer;
use Traversable;

/**
 * @implements IteratorAggregate<int, Writer>
 */
class WritersCollection implements IteratorAggregate
{
    /**
     * @param array<Writer> $standaloneWriters
     */
    public function __construct(
        protected Writer $typesWriter,
        protected array $standaloneWriters = [],
    ) {
    }

    public function getTypesWriter(): Writer
    {
        return $this->typesWriter;
    }

    /**
     * @return array<Writer>
     */
    public function getStandaloneWriters(): array
    {
        return $this->standaloneWriters;
    }

    public function addStandaloneWriter(Writer $writer): void
    {
        $hash = spl_object_hash($writer);

        foreach ($this->standaloneWriters as $existingWriter) {
            if (spl_object_hash($existingWriter) === $hash) {
                return;
            }
        }

        $this->standaloneWriters[] = $writer;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator([
            $this->typesWriter,
            ...$this->standaloneWriters,
        ]);
    }
}
