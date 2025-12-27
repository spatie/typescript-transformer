<?php

namespace Spatie\TypeScriptTransformer;

use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\TypeScriptTransformer\Actions\FormatTypeScriptAction;
use Spatie\TypeScriptTransformer\Actions\PersistTypesCollectionAction;
use Spatie\TypeScriptTransformer\Actions\ResolveSplitTypesCollectionsAction;
use Spatie\TypeScriptTransformer\Actions\ResolveTypesCollectionAction;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Symfony\Component\Finder\Finder;

class TypeScriptTransformer
{
    protected TypeScriptTransformerConfig $config;

    public static function create(TypeScriptTransformerConfig $config): self
    {
        return new self($config);
    }

    public function __construct(TypeScriptTransformerConfig $config)
    {
        $this->config = $config;
    }

    public function transform(): TypesCollection
    {
        if (($baseDir = $this->config->getSplitModulesBaseDir()) !== null) {
            // Delete all .dto.ts files in the directory
            if (is_dir($baseDir)) {
                $finder = (new Finder())
                    ->files()
                    ->in($baseDir)
                    ->name('*.dto.ts');

                foreach ($finder as $file) {
                    unlink($file->getRealPath());
                }
            }

            $typesCollections = (new ResolveSplitTypesCollectionsAction(
                new Finder(),
                $this->config,
            ))->execute();

            $sumCollection = new TypesCollection();
            foreach ($typesCollections as $namespace => $typesCollection) {
                foreach ($typesCollection as $type) {
                    $sumCollection[] = $type;
                }
            }

            foreach ($typesCollections as $namespace => $typesCollection) {
                $outputFile = rtrim($baseDir, '/') . '/' . $namespace . '.dto.ts';

                (new PersistTypesCollectionAction(
                    $this->config,
                    $outputFile,
                ))->execute($typesCollection, $sumCollection);

                (new FormatTypeScriptAction($this->config, $outputFile))->execute();

            }

            return $sumCollection;
        } else {
            $typesCollection = (new ResolveTypesCollectionAction(
                new Finder(),
                $this->config,
            ))->execute();

            (new PersistTypesCollectionAction($this->config, $this->config->getOutputFile()))->execute($typesCollection, $typesCollection);

            (new FormatTypeScriptAction($this->config, $this->config->getOutputFile()))->execute();

            return $typesCollection;
        }
    }

}
