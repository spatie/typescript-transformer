<?php

namespace Spatie\TypeScriptTransformer\Laravel\Commands;

use Illuminate\Console\Command;
use Spatie\TypeScriptTransformer\Laravel\Support\ConsoleLogger;
use Spatie\TypeScriptTransformer\Runners\Runner;
use Spatie\TypeScriptTransformer\Support\Console\MultiLogger;
use Spatie\TypeScriptTransformer\Support\Console\RayLogger;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class TransformTypeScriptCommand extends Command
{
    public $signature = 'typescript:transform {--watch : Watch for changes and re-transform} {--worker : (internal) Run the worker process)}';

    public $description = 'Transforms PHP to TypeScript';

    public function handle(): int
    {
        if (! app()->has(TypeScriptTransformerConfig::class)) {
            $this->error('Please, first publish the TypeScriptTransformerServiceProvider and configure it.');

            return self::FAILURE;
        }

        $runner = new Runner(
            fn (bool $watch) => 'artisan typescript:transform --worker '. ($watch ? '--watch ' : '')
        );

        return $runner->run(
            logger: new MultiLogger([
                new RayLogger(),
                new ConsoleLogger($this),
            ]),
            config: app(TypeScriptTransformerConfig::class),
            watch: $this->option('watch'),
            isWorker: $this->option('worker'),
            forceDirectWorker: false,
        );
    }
}
