<?php

namespace Spatie\TypeScriptTransformer\Laravel\Support;

use Illuminate\Console\Command;
use Spatie\TypeScriptTransformer\Support\Console\ConsoleLogger as BaseConsoleLogger;

class ConsoleLogger extends BaseConsoleLogger
{
    public function __construct(
        protected Command $command
    ) {
    }

    public function error(mixed $item, ?string $title = null): void
    {
        $this->command->error($this->formatTitle($title).$this->mixedToString($item));
    }

    public function info(mixed $item, ?string $title = null): void
    {
        $this->command->info($this->formatTitle($title).$this->mixedToString($item));
    }

    public function warn(mixed $item, ?string $title = null): void
    {
        $this->command->warn($this->formatTitle($title).$this->mixedToString($item));
    }

    public function debug(mixed $item, ?string $title = null): void
    {
        $this->command->line($this->formatTitle($title).$this->mixedToString($item));
    }

    protected function formatTitle(
        ?string $title = null
    ): string {
        return $title ? "[$title] " : '';
    }
}
