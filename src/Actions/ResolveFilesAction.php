<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Data\WriteableFile;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class ResolveFilesAction
{
    public function __construct(
        protected TypeScriptTransformerConfig $config,
    ) {
    }

    /**
     * @return array<WriteableFile>
     */
    public function execute(TransformedCollection $collection): array
    {
        $writeableFiles = [];

        $collection->ensureEachTransformedHasAWriter($this->config->typesWriter);

        foreach ($collection->getUniqueWriters() as $writer) {
            $transformed = $collection->getTransformedForWriter($writer);

            array_push($writeableFiles, ...$writer->output($transformed, $collection));
        }

        return $writeableFiles;
    }
}
