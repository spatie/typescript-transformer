<?php

namespace Spatie\TypeScriptTransformer\Laravel;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\TypeScriptTransformer\Laravel\Commands\TransformTypeScriptCommand;
use Spatie\TypeScriptTransformer\Laravel\Commands\WatchTypeScriptCommand;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class TypeScriptTransformerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('typescript-transformer')
            ->hasCommand(WatchTypeScriptCommand::class)
            ->hasCommand(TransformTypeScriptCommand::class);
    }

    public function bootingPackage(): void
    {
        // TODO: use a laravel config file or something better here

        $this->app->singleton(
            TypeScriptTransformerConfig::class,
            fn () => LaravelTypeScriptTransformerConfig::$defined
        );

        $this->app->singleton(
            TypeScriptTransformerLog::class,
            fn () => new TypeScriptTransformerLog(),
        );
    }
}
