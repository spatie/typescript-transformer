<?php

namespace Spatie\TypeScriptTransformer\Tests\Support;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Data\GlobalNamespaceResolvedReference;
use Spatie\TypeScriptTransformer\Data\ModuleImportResolvedReference;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;
use Spatie\TypeScriptTransformer\Writers\FlatModuleWriter;
use Spatie\TypeScriptTransformer\Writers\Writer;

class MemoryWriter implements Writer
{
    /** @var array<Transformed> */
    public static array $transformed;

    public static TransformedCollection $collection;

    public function output(array $transformed, TransformedCollection $transformedCollection): array
    {
        static::$transformed = $transformed;
        static::$collection = $transformedCollection;

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
        $writer = new FlatModuleWriter('memory.ts');

        [$writeableFile] = $writer->output(static::$transformed, static::$collection);

        return $writeableFile->contents;
    }

    public function resolveReference(Transformed $transformed): ModuleImportResolvedReference|GlobalNamespaceResolvedReference
    {
        return new ModuleImportResolvedReference(
            $transformed->getName(),
            'memory.ts'
        );
    }
}
