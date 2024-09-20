<?php

namespace Spatie\TypeScriptTransformer\Laravel\Commands;

use Illuminate\Console\Command;
use Spatie\TypeScriptTransformer\Laravel\Support\WrappedLaravelConsole;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class WatchTypeScriptCommand extends Command
{
    public $signature = 'typescript:watch';

    public $description = 'Keeps track of changes in your PHP files and automatically re-generates your TypeScript types';

    public function handle(): int
    {
        if (! app()->has(TypeScriptTransformerConfig::class)) {
            $this->error('Please, first publish the TypeScriptTransformerServiceProvider and configure it.');

            return self::FAILURE;
        }

        TypeScriptTransformer::create(
            config: app(TypeScriptTransformerConfig::class),
            console: new WrappedLaravelConsole($this),
            watch: true
        )->execute();

        $this->comment('Watching for changes...');

        return self::SUCCESS;
    }
}
