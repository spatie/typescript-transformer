<?php

namespace Spatie\TypeScriptTransformer\Support;

class TypeScriptTransformerLog
{
    public array $infoMessages = [];

    public array $warningMessages = [];

    public array $errorMessages = [];

    protected static self $instance;

    private function __construct()
    {
    }

    public static function resolve(): self
    {
        return self::$instance ??= new self();
    }

    public function info(string $message): self
    {
        $this->infoMessages[] = $message;

        return $this;
    }

    public function warning(string $message): self
    {
        $this->warningMessages[] = $message;

        return $this;
    }

    public function error(string $message): self
    {
        $this->errorMessages[] = $message;

        return $this;
    }
}
