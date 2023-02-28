<?php

namespace Spatie\TypeScriptTransformer\Laravel;

use Illuminate\Support\Arr;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\TypeScriptTransformer\Laravel\Commands\LaravelTypescriptTransformerCommand;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Spatie\TypeScriptTransformer\Writers\TypeDefinitionWriter;

class TypeScriptTransformerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-typescript-transformer')
            ->hasCommand(LaravelTypescriptTransformerCommand::class);
    }

    public function registeringPackage(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/typescript.php', 'typescript'
        );
    }

    public function packageRegistered(): void
    {
        $this->app->bind(
            TypeScriptTransformerConfig::class,
            fn () => TypeScriptTransformerConfig::create()
                ->autoDiscoverTypes(...Arr::wrap(config('typescript.auto_discover_types')))
                ->collectors(config('typescript.collectors'))
                ->transformers(config('typescript.transformers'))
                ->defaultTypeReplacements(config('typescript.default_type_replacements'))
                ->writer(config('typescript.writer'))
                ->outputFile(config('typescript.output_file'))
                ->writer(config('typescript.writer', TypeDefinitionWriter::class))
                ->formatter(config('typescript.formatter'))
                ->transformToNativeEnums(config('typescript.transform_to_native_enums', false))
        );
    }
}
