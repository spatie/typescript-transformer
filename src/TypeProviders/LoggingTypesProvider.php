<?php

namespace Spatie\TypeScriptTransformer\TypeProviders;

use Spatie\TypeScriptTransformer\Support\Console\Logger;

interface LoggingTypesProvider
{
    public function setLogger(Logger $logger): void;
}
