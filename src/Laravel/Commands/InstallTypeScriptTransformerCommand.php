<?php

namespace Spatie\TypeScriptTransformer\Laravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

/**
 * @note Taken from the Laravel Horizon package
 */
class InstallTypeScriptTransformerCommand extends Command
{
    public $signature = 'typescript:install';

    public $description = 'Installs TypeScript transformer within your Laravel application.';

    public function handle(): void
    {
        $this->comment('Publishing TypeScript Transformer Service Provider...');
        $this->callSilent('vendor:publish', ['--tag' => 'typescript-transformer-provider']);

        $this->registerTypescriptTransformerServiceProvider();

        $this->info('TypeScript Transformer scaffolding installed successfully.');
    }

    protected function registerTypescriptTransformerServiceProvider(): void
    {
        $namespace = Str::replaceLast('\\', '', $this->laravel->getNamespace());

        $appConfig = file_get_contents(config_path('app.php'));

        if (Str::contains($appConfig, $namespace.'\\Providers\\TypeScriptTransformerServiceProvider::class')) {
            return;
        }

        file_put_contents(config_path('app.php'), str_replace(
            "{$namespace}\\Providers\RouteServiceProvider::class,".PHP_EOL,
            "{$namespace}\\Providers\RouteServiceProvider::class,".PHP_EOL.PHP_EOL."{$namespace}\Providers\TypeScriptTransformerServiceProvider::class,".PHP_EOL,
            $appConfig
        ));

        file_put_contents(app_path('Providers/TypeScriptTransformerServiceProvider.php'), str_replace(
            "namespace App\Providers;",
            "namespace {$namespace}\Providers;",
            file_get_contents(app_path('Providers/TypeScriptTransformerServiceProvider.php'))
        ));
    }
}
