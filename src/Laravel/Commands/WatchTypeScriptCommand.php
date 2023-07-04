<?php

namespace Spatie\TypeScriptTransformer\Laravel\Commands;

use Illuminate\Console\Command;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Spatie\Watcher\Watch;

class WatchTypeScriptCommand extends Command
{
    public $signature = 'typescript:watch';

    public $description = 'Keeps track of changes in your PHP files and automatically re-generates your TypeScript types';

    public function handle(
        TypeScriptTransformerConfig $config,
    ): int {


        $this->comment('Watching for changes...');

        return self::SUCCESS;
    }
}
