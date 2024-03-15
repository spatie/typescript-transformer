<?php

namespace Spatie\TypeScriptTransformer\Laravel\Commands;

use Illuminate\Console\Command;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;
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

        app(TypeScriptTransformer::class)->execute();

        $log = TypeScriptTransformerLog::resolve();

        if (! empty($log->infoMessages)) {
            foreach ($log->infoMessages as $infoMessage) {
                $this->info($infoMessage);
            }
        }

        if (! empty($log->warningMessages)) {
            foreach ($log->warningMessages as $warningMessage) {
                $this->warn($warningMessage);
            }
        }

        $this->comment('All done');

        return self::SUCCESS;
    }
}
