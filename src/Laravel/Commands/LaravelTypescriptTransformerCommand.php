<?php

namespace Spatie\TypeScriptTransformer\Laravel\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Spatie\TypeScriptTransformer\Formatters\PrettierFormatter;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class LaravelTypescriptTransformerCommand extends Command
{
    use ConfirmableTrait;

    protected $signature = 'typescript:transform
                            {--force : Force the operation to run when in production}
                            {--path= : Specify a path with classes to transform}
                            {--output= : Use another file to output}
                            {--format : Use Prettier to format the output}';

    protected $description = 'Map PHP structures to TypeScript';

    public function handle(
        TypeScriptTransformerConfig $config
    ): int {
        $this->confirmToProceed();

        if ($inputPath = $this->resolveInputPath()) {
            $config->autoDiscoverTypes($inputPath);
        }

        if ($outputFile = $this->resolveOutputFile()) {
            $config->outputFile($outputFile);
        }

        if ($this->option('format')) {
            $config->formatter(PrettierFormatter::class);
        }

        $transformer = new TypeScriptTransformer($config);

        try {
            $this->ensureConfiguredCorrectly();

            $collection = $transformer->transform();
        } catch (Exception $exception) {
            $this->error($exception->getMessage());

            return 1;
        }

        $this->table(
            ['PHP class', 'TypeScript entity'],
            collect($collection)->map(fn(TransformedType $type, string $class) => [
                $class, $type->getTypeScriptName(),
            ])
        );

        $this->info("Transformed {$collection->count()} PHP types to TypeScript");

        return 0;
    }

    private function resolveInputPath(): ?string
    {
        $path = $this->option('path');

        if ($path === null) {
            return null;
        }

        if (file_exists($path)) {
            return $path;
        }

        return app_path($path);
    }

    private function resolveOutputFile(): ?string
    {
        $path = $this->option('output');

        if ($path === null) {
            return null;
        }

        return resource_path($path);
    }

    private function ensureConfiguredCorrectly()
    {
        if (config()->has('typescript-transformer.searching_path')) {
            throw new Exception('In v2 of laravel-typescript-transformer the `searching_path` key within the typescript-transformer.php config file is renamed to `auto_discover_types`');
        }
    }
}
