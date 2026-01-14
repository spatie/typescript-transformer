<?php

namespace Spatie\TypeScriptTransformer\Support\Loggers;

class MultiLogger implements Logger
{
    /**
     * @param Logger[] $loggers
     */
    public function __construct(
        public array $loggers,
    ) {
    }

    public function error(mixed $item, ?string $title = null): void
    {
        foreach ($this->loggers as $logger) {
            $logger->error($item, $title);
        }
    }

    public function info(mixed $item, ?string $title = null): void
    {
        foreach ($this->loggers as $logger) {
            $logger->info($item, $title);
        }
    }

    public function warn(mixed $item, ?string $title = null): void
    {
        foreach ($this->loggers as $logger) {
            $logger->warn($item, $title);
        }
    }

    public function debug(mixed $item, ?string $title = null): void
    {
        foreach ($this->loggers as $logger) {
            $logger->debug($item, $title);
        }
    }
}
