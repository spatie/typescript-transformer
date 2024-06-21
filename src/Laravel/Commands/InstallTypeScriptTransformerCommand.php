<?php

namespace Spatie\TypeScriptTransformer\Laravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

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

        $namespace = Str::replaceLast('\\', '', $this->laravel->getNamespace());

        $this->installServiceProvider($namespace);
        $this->registerServiceProvider($namespace);
    }

    protected function installServiceProvider(string $namespace): void
    {
        $serviceProviderPath = app_path('Providers/TypeScriptTransformerServiceProvider.php');

        if (file_exists($serviceProviderPath)) {
            $this->info('TypeScript Transformer Service Provider already installed.');

            return;
        }

        file_put_contents($serviceProviderPath, str_replace(
            "namespace App\Providers;",
            "namespace {$namespace}\Providers;",
            file_get_contents($serviceProviderPath)
        ));

        $this->info('TypeScript Transformer Service Provider installed.');
    }

    protected function registerServiceProvider(string $namespace): void
    {
        $configFile = version_compare($this->laravel->version(), '11.0.0', '>=') ?
            base_path('bootstrap/providers.php') :
            config_path('app.php');

        $appConfig = file_get_contents($configFile);

        if (Str::contains($appConfig, $namespace.'\\Providers\\TypeScriptTransformerServiceProvider::class')) {
            $this->info('TypeScript Transformer Service Provider already registered.');

            return;
        }

        file_put_contents($configFile, str_replace(
            "{$namespace}\\Providers\AppServiceProvider::class,".PHP_EOL,
            "{$namespace}\\Providers\AppServiceProvider::class,".PHP_EOL."    {$namespace}\Providers\TypeScriptTransformerServiceProvider::class,".PHP_EOL,
            $appConfig
        ));

        $this->info('TypeScript Transformer Service Provider registered.');
    }
}
