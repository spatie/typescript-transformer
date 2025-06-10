<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class PersistTypesCollectionAction
{
    protected TypeScriptTransformerConfig $config;

    public function __construct(TypeScriptTransformerConfig $config, protected string $outputFile)
    {
        $this->config = $config;
    }

    public function execute(TypesCollection $moduleCollection, ?TypesCollection $totalCollection = null): void
    {
        if ($totalCollection === null) {
            $totalCollection = $moduleCollection;
        }
        $this->ensureOutputFileExists();

        $writer = $this->config->getWriter();

        (new ReplaceIntermoduleSymbolsInCollectionAction())->execute(
            $moduleCollection,
            $totalCollection,
            $writer->replacesSymbolsWithFullyQualifiedIdentifiers()
        );

        file_put_contents(
            $this->outputFile,
            $writer->format($moduleCollection)
        );
    }

    protected function ensureOutputFileExists(): void
    {
        if (! file_exists(pathinfo($this->outputFile, PATHINFO_DIRNAME))) {
            mkdir(pathinfo($this->outputFile, PATHINFO_DIRNAME), 0755, true);
        }
    }
}
