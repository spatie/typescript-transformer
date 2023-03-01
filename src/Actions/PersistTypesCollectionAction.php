<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\FileSplitters\SingleFileSplitter;
use Spatie\TypeScriptTransformer\Structures\SplitTypesCollection;
use Spatie\TypeScriptTransformer\Structures\TypeImport;
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

        (new ReplaceTypeReferencesInCollectionAction())->execute(
            $collection,
            $writer->replacesSymbolsWithFullyQualifiedIdentifiers()
        );

        $splitter = $this->config->getFileSplitter();

        foreach ($splitter->split($this->config->getOutputPath(), $collection) as $split) {
            $this->ensureOutputFileExists($split->path);

            $imports = $this->resolveImports($split);

            $types = $writer->format($split->types);

            $contents = $imports === null
                ? $types
                : $imports . PHP_EOL . $types;

            if (empty($contents)) {
                continue;
            }

            file_put_contents($split->path, $contents);

            (new FormatTypeScriptAction($this->config))->execute($split->path);
        }
    }

    protected function ensureOutputFileExists(string $path): void
    {
        if (! file_exists(pathinfo($path, PATHINFO_DIRNAME))) {
            mkdir(pathinfo($path, PATHINFO_DIRNAME), 0755, true);
        }
    }

    private function resolveImports(SplitTypesCollection $split): ?string
    {
        if (empty($split->imports)) {
            return null;
        }

        return array_reduce(
            $split->imports,
            fn(string $carry, TypeImport $import) => "{$carry}{$import->toString()}" . PHP_EOL,
            ''
        );
    }
}
