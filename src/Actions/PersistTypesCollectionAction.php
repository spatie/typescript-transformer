<?php

namespace Spatie\TypescriptTransformer\Actions;

use Spatie\TypescriptTransformer\Type;
use Spatie\TypescriptTransformer\TypesCollection;
use Spatie\TypescriptTransformer\TypeScriptTransformerConfig;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PersistTypesCollectionAction
{
    private TypeScriptTransformerConfig $config;

    public function __construct(TypeScriptTransformerConfig $config)
    {
        $this->config = $config;
    }

    public function execute(TypesCollection $collection): void
    {
        $basePath = $this->resolveBasePath();

        foreach ($collection->get() as $file => $types) {
            $path = "{$basePath}{$file}";

            if (! File::exists(pathinfo($path, PATHINFO_DIRNAME))) {
                mkdir(pathinfo($path, PATHINFO_DIRNAME), 0755, true);
            }

            file_put_contents(
                $path,
                join(PHP_EOL, array_map(fn (Type $type) => $type->transformed, $types))
            );
        }
    }

    private function resolveBasePath(): string
    {
        $path = trim($this->config->getOutputPath());

        return Str::endsWith($path, '/') ? $path : "{$path}/";
    }
}
