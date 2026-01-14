<?php

namespace Spatie\TypeScriptTransformer\TransformedProviders;

use Spatie\TypeScriptTransformer\Support\Loggers\Logger;

interface LoggingTransformedProvider
{
    public function setLogger(Logger $logger): void;
}
