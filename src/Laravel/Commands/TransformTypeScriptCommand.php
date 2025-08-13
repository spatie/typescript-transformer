<?php

namespace Spatie\TypeScriptTransformer\Laravel\Commands;

use Illuminate\Console\Command;
use Spatie\TypeScriptTransformer\Laravel\Support\Logger;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class TransformTypeScriptCommand extends Command
{
    public $signature = 'typescript:transform';

    public $description = 'Transforms PHP to TypeScript';

    public function handle(): int
    {
        if (! app()->has(TypeScriptTransformerConfig::class)) {
            $this->error('Please, first publish the TypeScriptTransformerServiceProvider and configure it.');

            return self::FAILURE;
        }

        TypeScriptTransformer::create(
            config: app(TypeScriptTransformerConfig::class),
            console: new Logger($this)
        )->execute();

        $this->comment('All done');

        return self::SUCCESS;
    }
}
