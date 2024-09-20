<?php

namespace Spatie\TypeScriptTransformer\Support\Console;

class WrappedArrayConsole implements WrappedConsole
{
    /** @var array<array{message: string, level: "error"|"info"|"warning"}> */
    public array $messages = [];

    public function error(string $message): void
    {
        $this->messages[] = ['message' => $message, 'level' => 'error'];
    }

    public function info(string $message): void
    {
        $this->messages[] = ['message' => $message, 'level' => 'info'];
    }

    public function warn(string $message): void
    {
        $this->messages[] = ['message' => $message, 'level' => 'warning'];
    }

    public function exit(int $code = 0): void
    {
        die($code);
    }
}
