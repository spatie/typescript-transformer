<?php

namespace Spatie\TypescriptTransformer;

use Illuminate\Support\ServiceProvider;
use Spatie\TypescriptTransformer\Commands\MapOptionsToTypescriptCommand;

class TypescriptTransformerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MapOptionsToTypescriptCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__.'/../config/typescript-transformer.php' => config_path('typescript-transformer.php'),
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/typescript-transformer.php',
            'typescript-transformer'
        );

        $this->app->instance(TypeScriptTransformerConfig::class, new TypeScriptTransformerConfig(
            config('typescript-transformer.searching_path'),
            config('typescript-transformer.transformers'),
            config('typescript-transformer.default_file'),
            config('typescript-transformer.output_path'),
        ));
    }
}
