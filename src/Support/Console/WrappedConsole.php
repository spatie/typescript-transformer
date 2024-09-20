<?php

namespace Spatie\TypeScriptTransformer\Support\Console;

interface WrappedConsole
{
    public function error(string $message): void;

    public function info(string $message): void;

    public function warn(string $message): void;

    public function exit(int $code = 0): void;
}
