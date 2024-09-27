<?php

namespace Spatie\TypeScriptTransformer\Tests\Support;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;
use Spatie\TypeScriptTransformer\Writers\FlatWriter;
use Spatie\TypeScriptTransformer\Writers\Writer;

class MemoryWriter implements Writer
{
    public static TransformedCollection $collection;

    public function output(TransformedCollection $collection): array
    {
        static::$collection = $collection;

        return [];
    }

    public function getTransformedNodeByName(string $name): ?TypeScriptNode
    {
        foreach (static::$collection as $transformed) {
            if ($transformed->getName() === $name) {
                return $transformed->typeScriptNode;
            }
        }
    }

    public function getOutput(): string
    {
        $writer = new FlatWriter('test.ts');

        [$writeableFile] = $writer->output(static::$collection);

        return $writeableFile->contents;
    }
}
