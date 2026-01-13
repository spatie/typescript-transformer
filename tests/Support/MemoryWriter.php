<?php

namespace Spatie\TypeScriptTransformer\Tests\Support;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Data\GlobalNamespaceReferenced;
use Spatie\TypeScriptTransformer\Data\ImportedReferenced;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;
use Spatie\TypeScriptTransformer\Writers\FlatWriter;
use Spatie\TypeScriptTransformer\Writers\Writer;

class MemoryWriter implements Writer
{
    /** @var array<Transformed> */
    public static array $transformed;

    public static TransformedCollection $collection;

    public function output(array $transformed, TransformedCollection $collection): array
    {
        static::$transformed = $transformed;
        static::$collection = $collection;

        return [];
    }

    public function getTransformedNodeByName(string $name): ?TypeScriptNode
    {
        foreach (static::$transformed as $transformedItem) {
            if ($transformedItem->getName() === $name) {
                return $transformedItem->typeScriptNode;
            }
        }
    }

    public function getOutput(): string
    {
        $writer = new FlatWriter('test.ts');

        [$writeableFile] = $writer->output(static::$transformed, static::$collection);

        return $writeableFile->contents;
    }

    public function resolveReferenced(Transformed $transformed): ImportedReferenced|GlobalNamespaceReferenced
    {
        return new ImportedReferenced(
            $transformed->getName(),
            'memory.ts'
        );
    }
}
