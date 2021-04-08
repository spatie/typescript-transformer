<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class FormatTypeScriptAction
{
    protected TypeScriptTransformerConfig $config;

    public function __construct(TypeScriptTransformerConfig $config)
    {
        $this->config = $config;
    }

    public function execute(): void
    {
        $formatter = $this->config->getFormatter();

        if ($formatter === null) {
            return;
        }

        $formatter->format($this->config->getOutputFile());
    }
}
