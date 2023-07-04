<?php

namespace Spatie\TypeScriptTransformer\Laravel\Commands;

use Illuminate\Console\Command;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;

class TransformTypeScriptCommand extends Command
{
    public $signature = 'typescript:transform';

    public $description = 'Transforms PHP to TypeScript';

    public function handle(
        TypeScriptTransformer $typeScriptTransformer
    ): int {
        $log = $typeScriptTransformer->execute();

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
