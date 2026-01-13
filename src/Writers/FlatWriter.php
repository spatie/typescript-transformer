<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Actions\CleanupReferencesAction;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Data\GlobalNamespaceReferenced;
use Spatie\TypeScriptTransformer\Data\ImportedReferenced;
use Spatie\TypeScriptTransformer\Support\WriteableFile;
use Spatie\TypeScriptTransformer\Support\WritingContext;
use Spatie\TypeScriptTransformer\Transformed\Transformed;

class FlatWriter implements Writer
{
    protected CleanupReferencesAction $cleanupReferencesAction;

    public function __construct(
        public string $filename = 'types.ts',
    ) {
        $this->cleanupReferencesAction = new CleanupReferencesAction();
    }

    public function output(
        array $transformed,
        TransformedCollection $collection,
    ): array {
        [$imports, $nameMap] = $this->cleanupReferencesAction->execute(
            $this,
            $this->filename,
            $transformed,
            $collection
        );

        $output = '';

        $writingContext = new WritingContext($nameMap);

        foreach ($imports->getTypeScriptNodes() as $import) {
            $output .= $import->write($writingContext).PHP_EOL;
        }

        foreach ($transformed as $item) {
            $output .= $item->write($writingContext).PHP_EOL;
        }

        return [new WriteableFile($this->filename, $output)];
    }

    public function resolveReferenced(Transformed $transformed): ImportedReferenced|GlobalNamespaceReferenced
    {
        return new ImportedReferenced(
            $transformed->getName(),
            $this->filename
        );
    }
}
