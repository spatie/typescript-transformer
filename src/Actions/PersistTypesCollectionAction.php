<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\FileSplitters\SingleFileSplitter;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class PersistTypesCollectionAction
{
    protected TypeScriptTransformerConfig $config;

    public function __construct(TypeScriptTransformerConfig $config)
    {
        $this->config = $config;
    }

    public function execute(TypesCollection $collection): void
    {
        $writer = $this->config->getWriter();

        (new ReplaceSymbolsInCollectionAction())->execute(
            $collection,
            $writer->replacesSymbolsWithFullyQualifiedIdentifiers()
        );

        $splitter = $this->config->getFileSplitter();

        foreach ($splitter->split($this->config->getOutputPath(), $collection) as $split) {
            $this->ensureOutputFileExists($split->path);

            file_put_contents(
                $split->path,
                $writer->format($split->types)
            );

            (new FormatTypeScriptAction($this->config))->execute($split->path);
        }
    }

    protected function ensureOutputFileExists(string $path): void
    {
        if (! file_exists(pathinfo($path, PATHINFO_DIRNAME))) {
            mkdir(pathinfo($path, PATHINFO_DIRNAME), 0755, true);
        }
    }
}
