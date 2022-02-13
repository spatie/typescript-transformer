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

        $outputDestination = $this->config->getOutputDestination();

        if ($this->config->getOutput()->writesMultipleFiles()) {
            $outputDestination = is_file($outputDestination) ? dirname($outputDestination) : $outputDestination;
        }

        $formatter->format($outputDestination);
    }
}
