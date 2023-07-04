<?php

namespace Spatie\TypeScriptTransformer\Support;

class TypeScriptTransformerLog
{
    public array $infoMessages = [];

    public array $warningMessages = [];

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
}
