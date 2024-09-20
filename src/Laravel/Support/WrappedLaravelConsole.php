<?php

namespace Spatie\TypeScriptTransformer\Laravel\Support;

use Illuminate\Console\Command;
use Spatie\TypeScriptTransformer\Support\Console\WrappedConsole;

class WrappedLaravelConsole implements WrappedConsole
{
    public function __construct(
        protected Command $command
    ) {
    }

    public function error(string $message): void
    {
        $this->command->error($message);
    }

    public function info(string $message): void
    {
        $this->command->info($message);
    }

    public function warn(string $message): void
    {
        $this->command->warn($message);
    }

    public function exit(int $code = 0): void
    {
        exit($code);
    }
}
