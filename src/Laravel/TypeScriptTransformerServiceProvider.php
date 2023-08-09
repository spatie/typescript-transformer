<?php

namespace Spatie\TypeScriptTransformer\Laravel;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\TypeScriptTransformer\Laravel\Commands\InstallTypeScriptTransformerCommand;
use Spatie\TypeScriptTransformer\Laravel\Commands\TransformTypeScriptCommand;
use Spatie\TypeScriptTransformer\Laravel\Commands\WatchTypeScriptCommand;

class TypeScriptTransformerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('typescript-transformer')
            ->hasCommand(WatchTypeScriptCommand::class)
            ->hasCommand(TransformTypeScriptCommand::class)
            ->hasCommand(InstallTypeScriptTransformerCommand::class);
    }

    public function bootingPackage(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../stubs/TypeScriptTransformerServiceProvider.stub' => app_path('Providers/TypeScriptTransformerServiceProvider.php'),
            ], 'typescript-transformer-provider');
        }
    }
}
