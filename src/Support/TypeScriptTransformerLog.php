<?php

namespace Spatie\TypeScriptTransformer\Support;

use Spatie\TypeScriptTransformer\Support\Console\WrappedConsole;
use Spatie\TypeScriptTransformer\Support\Console\WrappedNullConsole;

class TypeScriptTransformerLog
{
    public function __construct(
        protected WrappedConsole $wrappedConsole,
    ) {
    }

    public static function createNullLog(): self
    {
        return new self(new WrappedNullConsole());
    }

    public function info(string $message): self
    {
        $this->wrappedConsole->info($message);

        return $this;
    }

    public function warning(string $message): self
    {
        $this->wrappedConsole->warn($message);

        return $this;
    }

    public function error(string $message): self
    {
        $this->wrappedConsole->error($message);

        return $this;
    }
}
