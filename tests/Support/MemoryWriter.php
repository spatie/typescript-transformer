<?php

namespace Spatie\TypeScriptTransformer\Tests\Support;

use Spatie\TypeScriptTransformer\Collections\ReferenceMap;
use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;
use Spatie\TypeScriptTransformer\Writers\FlatWriter;
use Spatie\TypeScriptTransformer\Writers\Writer;

class MemoryWriter implements Writer
{
    public static TransformedCollection $collection;

    public static ReferenceMap $referenceMap;

    public function output(TransformedCollection $collection, ReferenceMap $referenceMap): array
    {
        static::$collection = $collection;
        static::$referenceMap = $referenceMap;

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

        [$writeableFile] = $writer->output(static::$collection, static::$referenceMap);

        return $writeableFile->contents;
    }
}
