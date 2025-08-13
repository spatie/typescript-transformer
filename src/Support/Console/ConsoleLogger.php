<?php

namespace Spatie\TypeScriptTransformer\Support\Console;

class ConsoleLogger implements Logger
{
    /** @var array<array{message: string, title: string|null, level: "error"|"info"|"warning"}> */
    public array $messages = [];

    public function error(mixed $item, ?string $title = null): void
    {
        $this->messages[] = ['message' => $this->mixedToString($item), 'title' => $title, 'level' => 'error'];
    }

    public function info(mixed $item, ?string $title = null): void
    {
        $this->messages[] = ['message' => $this->mixedToString($item), 'title' => $title, 'level' => 'info'];
    }

    public function warn(mixed $item, ?string $title = null): void
    {
        $this->messages[] = ['message' => $this->mixedToString($item), 'title' => $title, 'level' => 'warning'];
    }

    public function debug(mixed $item, ?string $title = null): void
    {
        $this->messages[] = ['message' => $this->mixedToString($item), 'title' => $title, 'level' => 'debug'];
    }

    protected function mixedToString(mixed $item): string
    {
        if ($item === null) {
            return 'null';
        }

        if (is_numeric($item) || is_string($item)) {
            return (string) $item;
        }

        if (is_bool($item)) {
            return $item ? 'true' : 'false';
        }

        $type = is_object($item)
            ? get_class($item)
            : gettype($item);

        return "({$type}) " .json_encode($item, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
