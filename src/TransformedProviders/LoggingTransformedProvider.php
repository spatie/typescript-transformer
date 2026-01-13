<?php

namespace Spatie\TypeScriptTransformer\TransformedProviders;

use Spatie\TypeScriptTransformer\Support\Console\Logger;

interface LoggingTransformedProvider
{
    public function setLogger(Logger $logger): void;
}
