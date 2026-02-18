<?php

namespace Spatie\TypeScriptTransformer\Tests\TestSupport;

use Spatie\TypeScriptTransformer\Support\Loggers\Logger;

class ArrayLogger implements Logger
{
    public function __construct(
        public array $logs,
    ) {
    }

    public function error(mixed $item, ?string $title = null): void
    {
        $this->logs[] = [
            'level' => 'error',
            'title' => $title,
            'item' => $item,
        ];
    }

    public function info(mixed $item, ?string $title = null): void
    {
        $this->logs[] = [
            'level' => 'info',
            'title' => $title,
            'item' => $item,
        ];
    }

    public function warning(mixed $item, ?string $title = null): void
    {
        $this->logs[] = [
            'level' => 'warn',
            'title' => $title,
            'item' => $item,
        ];
    }

    public function debug(mixed $item, ?string $title = null): void
    {
        $this->logs[] = [
            'level' => 'debug',
            'title' => $title,
            'item' => $item,
        ];
    }
}
