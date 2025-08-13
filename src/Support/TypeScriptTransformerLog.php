<?php

namespace Spatie\TypeScriptTransformer\Support;

use RuntimeException;
use Spatie\TypeScriptTransformer\Support\Console\Logger;
use Spatie\TypeScriptTransformer\Support\Console\NullLogger;

class TypeScriptTransformerLog
{
    protected static $self;

    protected function __construct(
        protected Logger $logger,
    ) {
    }

    public static function create(Logger $logger): self
    {
        if (isset(static::$self)) {
            throw new RuntimeException('TypeScriptTransformerLog instance already created.');
        }

        return static::$self = new static($logger);
    }

    public static function instance(): self
    {
        if (! isset(static::$self)) {
            throw new RuntimeException('TypeScriptTransformerLog instance not created.');
        }

        return static::$self;
    }

    public static function createNullLog(): self
    {
        return new self(new NullLogger());
    }

    public function info(mixed $item, ?string $title = null): self
    {
        $this->logger->info($item, $title);

        return $this;
    }

    public function warning(mixed $item, ?string $title = null): self
    {
        $this->logger->warn($item, $title);

        return $this;
    }

    public function error(mixed $item, ?string $title = null): self
    {
        $this->logger->error($item, $title);

        return $this;
    }

    public function debug(mixed $item, ?string $title = null): self
    {
        $this->logger->debug($item, $title);

        return $this;
    }
}
