<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Collections\WritersCollection;
use Spatie\TypeScriptTransformer\Data\WriteableFile;

class ResolveFilesAction
{
    /**
     * @return array<WriteableFile>
     */
    public function execute(
        TransformedCollection $collection,
        WritersCollection $writersCollection,
    ): array {
        $writeableFiles = [];

        foreach ($writersCollection as $writer) {
            $transformed = $collection->getTransformedForWriter($writer);

            array_push($writeableFiles, ...$writer->output($transformed, $collection));
        }

        return $writeableFiles;
    }
}
